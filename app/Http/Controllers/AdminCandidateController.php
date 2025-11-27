<?php

namespace App\Http\Controllers;

use Google\Client;
use Google\Service\Sheets;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use App\Support\IdGenerator;
use App\Models\TaiKhoan;
use App\Models\NhanVien;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;

class AdminCandidateController extends Controller
{
    public function index(Request $request)
    {
        try {
            [$headers, $rows] = $this->readSheet();
        } catch (\Throwable $e) {
            Log::error('Google Sheet read error', ['error' => $e->getMessage()]);

            return view('admin.candidates.index', [
                'candidates' => new LengthAwarePaginator([], 0, 20),
                'positions' => collect(),
                'statuses' => collect(),
                'headers' => collect(),
            ])->with('error', 'Không đọc được dữ liệu từ Google Sheet. Vui lòng kiểm tra cấu hình.');
        }

        $positionHeader = $this->findHeader($headers, ['position', 'vi_tri', 'vi_tri_cong_viec']);
        $statusHeader = $this->findHeader($headers, ['status', 'trang_thai']);
        $nameHeader = $this->findHeader($headers, ['name', 'ho_ten', 'ho va ten', 'ten']);
        $phoneHeader = $this->findHeader($headers, ['phone', 'sdt', 'so_dien_thoai']);
        $emailHeader = $this->findHeader($headers, ['email']);
        $genderHeader = $this->findHeader($headers, ['gender', 'gioi_tinh']);
        $dobHeader = $this->findHeader($headers, ['dob', 'ngay_sinh']);
        $addressHeader = $this->findHeader($headers, ['address', 'dia_chi']);
        $workAreaHeader = $this->findHeader($headers, ['khu_vuc_lam_viec', 'khu_vuc', 'khu vực làm việc', 'experience', 'kinh_nghiem']);

        // Dedupe by phone + email to tránh hiển thị trùng
        $rows = $rows->unique(function (array $row) use ($phoneHeader, $emailHeader) {
            $phone = $phoneHeader && isset($row[$phoneHeader]) ? preg_replace('/\\D+/', '', (string) $row[$phoneHeader]) : '';
            $email = $emailHeader && isset($row[$emailHeader]) ? mb_strtolower(trim((string) $row[$emailHeader])) : '';
            $key = trim($phone . '|' . $email, '|');
            return $key !== '' ? $key : md5(json_encode($row));
        })->values();

        // Emails đã duyệt (đã có nhân viên)
        $emailList = $rows->map(function ($row) use ($emailHeader) {
            return $emailHeader && isset($row[$emailHeader]) ? mb_strtolower(trim((string) $row[$emailHeader])) : null;
        })->filter()->unique()->values();
        $approvedEmails = NhanVien::whereIn('Email', $emailList)->pluck('Email')->map(fn($e) => mb_strtolower($e))->toArray();
        $approvalFilter = $request->get('approved');

        $filtered = $rows
            ->when($q = trim((string) $request->q), function (Collection $collection) use ($q, $nameHeader, $phoneHeader, $emailHeader) {
                $needle = mb_strtolower($q);

                return $collection->filter(function (array $row) use ($needle, $nameHeader, $phoneHeader, $emailHeader) {
                    $haystacks = [];
                    foreach ([$nameHeader, $phoneHeader, $emailHeader] as $key) {
                        if ($key && array_key_exists($key, $row)) {
                            $haystacks[] = $row[$key];
                        }
                    }

                    if (empty($haystacks)) {
                        $haystacks = array_values($row);
                    }

                    foreach ($haystacks as $value) {
                        if (str_contains(mb_strtolower((string) $value), $needle)) {
                            return true;
                        }
                    }

                    return false;
                });
            })
            ->when(in_array($approvalFilter, ['approved', 'pending'], true), function (Collection $collection) use ($approvedEmails, $emailHeader, $approvalFilter) {
                return $collection->filter(function (array $row) use ($approvedEmails, $emailHeader, $approvalFilter) {
                    $email = $emailHeader && isset($row[$emailHeader]) ? mb_strtolower(trim((string) $row[$emailHeader])) : null;
                    $isApproved = $email && in_array($email, $approvedEmails, true);
                    return $approvalFilter === 'approved' ? $isApproved : !$isApproved;
                });
            })
            ->when($request->position && $positionHeader, fn(Collection $c) => $c->where($positionHeader, $request->position))
            ->when($request->status && $statusHeader, fn(Collection $c) => $c->where($statusHeader, $request->status));

        $page = LengthAwarePaginator::resolveCurrentPage();
        $perPage = 20;

        $paginated = new LengthAwarePaginator(
            $filtered->forPage($page, $perPage)->values(),
            $filtered->count(),
            $perPage,
            $page,
            ['path' => $request->url(), 'query' => $request->query()]
        );

        $positions = $positionHeader
            ? $rows->pluck($positionHeader)->filter()->unique()->sort()->values()
            : collect();
        $statuses = $statusHeader
            ? $rows->pluck($statusHeader)->filter()->unique()->sort()->values()
            : collect();

        return view('admin.candidates.index', [
            'candidates' => $paginated,
            'positions' => $positions,
            'statuses' => $statuses,
            'headers' => collect($headers),
            'nameHeader' => $nameHeader,
            'phoneHeader' => $phoneHeader,
            'emailHeader' => $emailHeader,
            'positionHeader' => $positionHeader,
            'statusHeader' => $statusHeader,
            'genderHeader' => $genderHeader,
            'dobHeader' => $dobHeader,
            'addressHeader' => $addressHeader,
            'workAreaHeader' => $workAreaHeader,
            'approvedEmails' => $approvedEmails,
            'approvalFilter' => $approvalFilter,
        ]);
    }

    public function sync()
    {
        Cache::forget('candidates.sheet.raw');
        return redirect()->route('admin.candidates.index')->with('success', 'Đã đồng bộ dữ liệu mới nhất từ Google Sheet.');
    }

    /**
     * Đọc dữ liệu Google Sheet và trả về [headers, rows], cache ngắn hạn để giảm số lần gọi.
     */
    private function readSheet(): array
    {
        $spreadsheetId = env('GOOGLE_SHEET_ID');
        $credentialsPath = env('GOOGLE_SERVICE_ACCOUNT_JSON');
        $range = env('GOOGLE_SHEET_RANGE', 'A1:Z'); // bao gồm header ở dòng 1

        if (!$spreadsheetId || !$credentialsPath) {
            throw new \RuntimeException('Chưa cấu hình GOOGLE_SHEET_ID hoặc GOOGLE_SERVICE_ACCOUNT_JSON');
        }

        // Allow absolute path (Windows drive or Unix) or relative path under project
        if (preg_match('/^[A-Za-z]:\\\\|^\\//', $credentialsPath)) {
            $credentialsFullPath = $credentialsPath;
        } else {
            $credentialsFullPath = base_path($credentialsPath);
        }
        if (!file_exists($credentialsFullPath)) {
            throw new \RuntimeException('Không tìm thấy file credentials: ' . $credentialsFullPath);
        }

        return Cache::remember('candidates.sheet.raw', now()->addMinutes(3), function () use ($spreadsheetId, $credentialsFullPath, $range) {
            $client = new Client();
            $client->setAuthConfig($credentialsFullPath);
            $client->addScope(Sheets::SPREADSHEETS_READONLY);
            $service = new Sheets($client);

            $response = $service->spreadsheets_values->get($spreadsheetId, $range);
            $values = $response->getValues() ?? [];

            if (empty($values)) {
                return [[], collect()];
            }

            $headerRow = array_map('trim', array_shift($values));
            $headerRow = array_filter($headerRow, fn($h) => $h !== '');

            if (empty($headerRow)) {
                $maxColumns = 0;
                foreach ($values as $row) {
                    $maxColumns = max($maxColumns, count($row));
                }
                $headerRow = array_map(fn($i) => 'Column ' . ($i + 1), range(0, $maxColumns - 1));
            }

            $rows = collect($values)->map(fn(array $row) => $this->zipRow($headerRow, $row));

            return [$headerRow, $rows];
        });
    }

    /**
     * Tìm header khớp với các từ khóa (so khớp bằng slug để bỏ dấu/khoảng trắng).
     */
    private function findHeader(array $headers, array $candidates): ?string
    {
        $normalizedHeaders = [];
        foreach ($headers as $header) {
            $normalizedHeaders[$header] = Str::slug(mb_strtolower($header), '_');
        }

        foreach ($candidates as $expected) {
            $expectedSlug = Str::slug(mb_strtolower($expected), '_');
            foreach ($normalizedHeaders as $original => $slug) {
                if ($slug === $expectedSlug) {
                    return $original;
                }
            }
        }

        return null;
    }

    /**
     * Gộp một dòng dữ liệu với header tương ứng.
     */
    private function zipRow(array $headers, array $row): array
    {
        $assoc = [];
        foreach ($headers as $index => $header) {
            $assoc[$header] = isset($row[$index]) ? trim((string) $row[$index]) : '';
        }

        return $assoc;
    }

    public function approve(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'full_name' => ['nullable', 'string', 'max:255'],
            'email' => ['required', 'email'],
            'phone' => ['required', 'string', 'max:20'],
            'gender' => ['nullable', 'string', 'max:50'],
            'dob' => ['nullable', 'string', 'max:50'],
            'work_area' => ['nullable', 'string', 'max:255'],
            'address' => ['nullable', 'string', 'max:255'],
            'position' => ['nullable', 'string', 'max:255'],
        ], [], [
            'full_name' => 'Họ tên',
            'email' => 'Email',
            'phone' => 'Số điện thoại',
            'gender' => 'Giới tính',
            'dob' => 'Ngày sinh',
        ]);

        if ($validator->fails()) {
            return back()->with('error', $validator->errors()->first());
        }
        $data = $validator->validated();
        $data['full_name'] = trim($data['full_name'] ?? '');
        if ($data['full_name'] === '') {
            // Đừng đẩy email vào tên; ưu tiên SĐT, nếu vẫn trống thì gán nhãn mặc định
            $fallbackName = trim((string) ($data['phone'] ?? ''));
            $data['full_name'] = $fallbackName !== '' ? $fallbackName : 'Ứng viên';
        }

        $account = TaiKhoan::where('email', $data['email'])->first();

        if (!$account) {
            return back()->with('error', 'Không tìm thấy tài khoản ứng viên theo email: ' . $data['email']);
        }

        try {
            DB::transaction(function () use ($data, $account) {
            // Activate account
            $account->TrangThaiTK = 'active';
            $account->save();

            // Create or update NhanVien
            $nhanVien = NhanVien::where('ID_TK', $account->ID_TK)->first();
                $dob = null;
                if (!empty($data['dob'])) {
                    try {
                        $dob = Carbon::parse($data['dob'])->format('Y-m-d');
                    } catch (\Throwable $e) {
                        $dob = null;
                    }
                }

                // Normalize gender to DB enum
                $gender = $data['gender'] ?? null;
                $genderLower = $gender ? mb_strtolower(trim($gender)) : null;
                $genderDb = null;
                if (in_array($genderLower, ['male', 'nam'])) {
                    $genderDb = 'male';
                } elseif (in_array($genderLower, ['female', 'nu', 'nữ'])) {
                    $genderDb = 'female';
                }

                $nvPayload = [
                    'Ten_NV' => $data['full_name'],
                    'SDT' => $data['phone'],
                    'Email' => $data['email'],
                    'GioiTinh' => $genderDb,
                    'NgaySinh' => $dob,
                    'KhuVucLamViec' => $data['work_area'] ?? ($data['address'] ?? null),
                    'TrangThai' => 'active',
                    'SoDu' => 0,
                ];

            if (!$nhanVien) {
                $idNv = IdGenerator::next('NhanVien', 'ID_NV', 'NV_');
                    NhanVien::create(array_merge($nvPayload, [
                        'ID_NV' => $idNv,
                        'ID_TK' => $account->ID_TK,
                    ]));
            } else {
                    $nhanVien->update($nvPayload);
            }
            });
        } catch (\Throwable $e) {
            Log::error('Approve candidate failed', ['error' => $e->getMessage()]);
            return back()->with('error', 'Duyệt hồ sơ thất bại: ' . $e->getMessage());
        }

        try {
            Mail::raw(
                "Chào {$data['full_name']},\n\nHồ sơ của bạn đã được duyệt và tài khoản đã được kích hoạt. Bạn có thể đăng nhập và bắt đầu làm việc.\n\nTrân trọng,\nbTaskee",
                function ($message) use ($data) {
                    $message->to($data['email']);
                    $message->subject('Hồ sơ đã được duyệt');
                }
            );
        } catch (\Throwable $e) {
            Log::warning('Gửi email duyệt hồ sơ thất bại', ['error' => $e->getMessage(), 'email' => $data['email']]);
        }

        return back()->with('success', 'Đã duyệt hồ sơ và kích hoạt tài khoản cho: ' . $data['full_name']);
    }
}
