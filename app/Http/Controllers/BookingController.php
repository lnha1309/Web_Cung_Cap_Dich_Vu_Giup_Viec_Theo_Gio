<?php

namespace App\Http\Controllers;

use App\Models\DanhGiaNhanVien;
use App\Models\DiaChi;
use App\Models\DichVu;
use App\Models\DonDat;
use App\Models\ChiTietKhuyenMai;
use App\Models\KhuyenMai;
use App\Models\LichLamViec;
use App\Models\LichSuThanhToan;
use App\Models\Quan;
use App\Services\VNPayService;
use App\Support\IdGenerator;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class BookingController extends Controller
{
    public function selectAddress()
    {
        $addresses = [];

        if (Auth::check() && Auth::user()->khachHang) {
            $addresses = Auth::user()
                ->khachHang
                ->diaChis()
                ->where('is_Deleted', false)
                ->orderBy('ID_DC')
                ->take(3)
                ->get();
        }

        return view('select-address', [
            'addresses' => $addresses,
        ]);
    }

    public function show(Request $request)
    {
        $addressText = $request->query('address');

        return view('booking', [
            'selectedAddress' => $addressText,
        ]);
    }

    public function quoteHour(Request $request)
    {
        $validated = $request->validate([
            'duration' => ['required', 'integer', 'in:2,3,4'],
        ]);

        $duration = (int) $validated['duration'];

        $idDv = match ($duration) {
            2 => 'DV001',
            3 => 'DV002',
            4 => 'DV003',
            default => null,
        };

        if ($idDv === null) {
            return response()->json(['error' => 'Thoi luong khong hop le'], 422);
        }

        $service = DichVu::findOrFail($idDv);

        return response()->json([
            'id_dv'    => $service->ID_DV,
            'ten_dv'   => $service->TenDV,
            'gia'      => (float) $service->GiaDV,
            'duration' => (float) $service->ThoiLuong,
        ]);
    }

    public function findStaff(Request $request)
    {
        $validated = $request->validate([
            'ngay_lam'    => ['required', 'date'],
            'gio_bat_dau' => ['required', 'date_format:H:i'],
            'thoi_luong'  => ['required', 'integer', 'min:1'],
            'dia_chi'     => ['nullable', 'string'],
        ]);

        $ngayLam = $validated['ngay_lam'];
        $gioBatDau = $validated['gio_bat_dau'];
        $thoiLuong = (int) $validated['thoi_luong'];

        $start = Carbon::createFromFormat('H:i', $gioBatDau);
        $gioKetThuc = $start->copy()->addHours($thoiLuong)->format('H:i:s');
        $gioBatDauSql = $start->format('H:i:s');

        $lich = LichLamViec::with('nhanVien')
            ->where('NgayLam', $ngayLam)
            ->where('TrangThai', 'ready')
            ->where('GioBatDau', '<=', $gioBatDauSql)
            ->where('GioKetThuc', '>=', $gioKetThuc)
            ->get();

        $customerQuan = null;
        $diaChiText = $validated['dia_chi'] ?? '';
        if ($diaChiText !== '') {
            $customerQuan = $this->guessQuanFromAddress($diaChiText);
        }

        $results = [];

        foreach ($lich as $item) {
            $nv = $item->nhanVien;
            if (!$nv) {
                continue;
            }

            $avgScore = DanhGiaNhanVien::where('ID_NV', $nv->ID_NV)->avg('Diem');
            $ratingPercent = $avgScore ? round(((float) $avgScore) / 5 * 100) : 30;

            $proximityPercent = 50;
            if ($customerQuan) {
                if ($nv->ID_Quan === $customerQuan->ID_Quan) {
                    $proximityPercent = 100;
                } elseif (
                    $nv->KhuVucLamViec &&
                    mb_stripos($nv->KhuVucLamViec, $customerQuan->TenQuan) !== false
                ) {
                    $proximityPercent = 80;
                } else {
                    $nvQuan = $nv->ID_Quan ? Quan::find($nv->ID_Quan) : null;
                    if (
                        $nvQuan &&
                        $nvQuan->ViDo !== null &&
                        $nvQuan->KinhDo !== null &&
                        $customerQuan->ViDo !== null &&
                        $customerQuan->KinhDo !== null
                    ) {
                        $distKm = $this->distanceKm(
                            (float) $nvQuan->ViDo,
                            (float) $nvQuan->KinhDo,
                            (float) $customerQuan->ViDo,
                            (float) $customerQuan->KinhDo
                        );
                        $proximityPercent = max(0, 100 - (int) round($distKm * 10));
                    }
                }
            }

            $score = $ratingPercent * 0.3 + $proximityPercent * 0.7;

            $jobsCompleted = DonDat::where('ID_NV', $nv->ID_NV)
                ->where('TrangThaiDon', 'done')
                ->count();

            $results[] = [
                'id_nv'             => $nv->ID_NV,
                'ten_nv'            => $nv->Ten_NV,
                'hinh_anh'          => $nv->HinhAnh,
                'sdt'               => $nv->SDT,
                'rating_percent'    => $ratingPercent,
                'proximity_percent' => $proximityPercent,
                'score'             => (float) $score,
                'jobs_completed'    => $jobsCompleted,
            ];
        }

        usort($results, static function (array $a, array $b): int {
            return $b['score'] <=> $a['score'];
        });

        return response()->json($results);
    }

    public function applyVoucher(Request $request)
    {
        $validated = $request->validate([
            'code'   => ['required', 'string'],
            'amount' => ['required', 'numeric', 'min:0'],
        ]);

        $code = $validated['code'];
        $amount = (float) $validated['amount'];

        $account  = Auth::user();
        $customer = $account?->khachHang;

        if (!$customer) {
            return response()->json(['error' => 'Vui long dang nhap truoc khi ap ma khuyen mai.'], 403);
        }

        $km = KhuyenMai::where('ID_KM', $code)
            ->where('TrangThai', 'activated')
            ->first();

        if (!$km) {
            return response()->json(['error' => 'Ma khuyen mai khong hop le hoac da ngung hoat dong.'], 422);
        }

        $today = Carbon::today();
        if (($km->NgayBatDau && $today->lt(Carbon::parse($km->NgayBatDau))) ||
            ($km->NgayKetThuc && $today->gt(Carbon::parse($km->NgayKetThuc)))) {
            return response()->json(['error' => 'Ma khuyen mai da het han.'], 422);
        }

        // Khong cho khach hang ap lai ma da dung truoc do
        if ($this->voucherUsedByCustomer($customer->ID_KH, $code)) {
            return response()->json(['error' => 'Ma khuyen mai nay ban da su dung truoc do nen khong the ap lai.'], 422);
        }

        $discount = $amount * ((float) $km->PhanTramGiam / 100);
        if ($km->GiamToiDa !== null) {
            $discount = min($discount, (float) $km->GiamToiDa);
        }

        $final = max(0, $amount - $discount);

        return response()->json([
            'id_km'           => $km->ID_KM,
            'ten_km'          => $km->Ten_KM,
            'percent'         => (float) $km->PhanTramGiam,
            'discount_amount' => (float) $discount,
            'final_amount'    => (float) $final,
        ]);
    }

    public function confirm(Request $request, VNPayService $vnPay)
    {
        $validated = $request->validate([
            'payment_method' => ['nullable', 'in:cash,vnpay'],
            'loai_don'       => ['required', 'in:hour,month'],
            'id_dv'          => ['required', 'string'],
            'id_dc'          => ['nullable', 'string'],
            'dia_chi_text'   => ['nullable', 'string'],
            'dia_chi_unit'   => ['nullable', 'string'],
            'dia_chi_street' => ['nullable', 'string'],
            'ngay_lam'       => ['nullable', 'date'],
            'gio_bat_dau'    => ['nullable', 'date_format:H:i'],
            'thoi_luong'     => ['nullable', 'integer', 'min:1'],
            'tong_tien'      => ['required', 'numeric', 'min:0'],
            'tong_sau_giam'  => ['nullable', 'numeric', 'min:0'],
            'id_nv'          => ['nullable', 'string'],
            'id_km'          => ['nullable', 'string'],
            'vouchers'      => ['nullable'], // Chap nhan mang hoac JSON string, se tu xu ly ben duoi
            'ghi_chu'        => ['nullable', 'string'],
        ]);

        $paymentMethod = $validated['payment_method'] ?? 'vnpay';

        $account = Auth::user();
        $customer = $account?->khachHang;

        if (!$customer) {
            return response()->json(['error' => 'Khong tim thay khach hang.'], 403);
        }

        $prefix = $validated['loai_don'] === 'month'
            ? 'DD_month_'
            : 'DD_hour_';
        $idDon = IdGenerator::next('DonDat', 'ID_DD', $prefix);

        
        $singleVoucher   = $validated['id_km'] ?? null;

        // Thu nhan vouchers (luon doc tu raw input de tranh bi filter bo)
        $appliedVouchers = [];
        $rawVouchers = $request->input('vouchers', []);
        if (is_string($rawVouchers)) {
            $decoded = json_decode($rawVouchers, true);
            if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                $rawVouchers = $decoded;
            }
        }
        if (is_array($rawVouchers)) {
            foreach ($rawVouchers as $voucher) {
                if (is_string($voucher)) {
                    $appliedVouchers[] = [
                        'id_km'     => $voucher,
                        'tien_giam' => null,
                    ];
                } elseif (is_array($voucher)) {
                    $code = $voucher['id_km'] ?? $voucher['code'] ?? $voucher['ma'] ?? null;
                    if (!$code) {
                        continue;
                    }
                    $amount = $voucher['tien_giam'] ?? $voucher['discount_amount'] ?? $voucher['amount'] ?? null;
                    $appliedVouchers[] = [
                        'id_km'     => $code,
                        'tien_giam' => is_numeric($amount) ? (float) $amount : null,
                    ];
                }
            }
        }

// Chan su dung lai ma khuyen mai o buoc confirm (phong truong hop front-end bo qua API applyVoucher)
        if (!empty($singleVoucher)) {
            if ($this->voucherUsedByCustomer($customer->ID_KH, $singleVoucher)) {
                return response()->json([
                    'error' => 'Ma khuyen mai ' . $singleVoucher . ' ban da su dung truoc do nen khong the ap lai.',
                ], 422);
            }
        }

        if (!empty($appliedVouchers)) {
            foreach ($appliedVouchers as $voucher) {
                if (empty($voucher['id_km'])) {
                    continue;
                }

                $code = $voucher['id_km'];
                if ($this->voucherUsedByCustomer($customer->ID_KH, $code)) {
                    return response()->json([
                        'error' => 'Ma khuyen mai ' . $code . ' ban da su dung truoc do nen khong the ap lai.',
                    ], 422);
                }
            }
        }

        $tongTien = (float) $validated['tong_tien'];
        $tongSauGiam = isset($validated['tong_sau_giam']) && $validated['tong_sau_giam'] !== null
            ? (float) $validated['tong_sau_giam']
            : $tongTien;

        $gioBatDau = $validated['gio_bat_dau'] ?? null;
        $selectedStaffId = $validated['id_nv'] ?? null;

        // Xy ly dia chi: tim ID_DC phu hop hoac tao moi (dia chi don le, khong bat buoc la dia chi da luu)
        $idDc = $validated['id_dc'] ?? null;
        $diaChiText = isset($validated['dia_chi_text'])
            ? trim((string) $validated['dia_chi_text'])
            : '';

        if ($diaChiText !== '') {
            // Tu front-end: dia_chi_text co the la chuoi gop unit + street.
            // O day uu tien 2 truong rieng neu co: dia_chi_unit, dia_chi_street.
            $rawUnit   = $validated['dia_chi_unit']   ?? null;
            $rawStreet = $validated['dia_chi_street'] ?? null;

            $canHo = $rawUnit !== null && trim($rawUnit) !== ''
                ? trim($rawUnit)
                : null;

            $full = $rawStreet !== null && trim($rawStreet) !== ''
                ? trim($rawStreet)
                : $diaChiText;

            // Khong tao/cap nhat dia chi da luu cua khach o day.
            // Chi tim xem dia chi nay co trong danh sach dia chi da luu (chua bi xoa mem) hay khong, neu co thi tai su dung ID_DC.
            $query = $customer->diaChis()
                ->where('is_Deleted', false)
                ->where('DiaChiDayDu', $full);
            if ($canHo !== null) {
                $query->where('CanHo', $canHo);
            }

            $existingAddress = $query->first();

            if ($existingAddress) {
                $idDc = $existingAddress->ID_DC;
            } else {
                $quan = $this->guessQuanFromAddress($full);
                $newIdDc = IdGenerator::next('DiaChi', 'ID_DC', 'DC_');

                DiaChi::create([
                    'ID_DC'       => $newIdDc,
                    'ID_KH'       => null,
                    'ID_Quan'     => $quan?->ID_Quan,
                    'CanHo'       => $canHo,
                    'DiaChiDayDu' => $full,
                ]);

                $idDc = $newIdDc;
            }
        }

        $trangThaiDon = $selectedStaffId ? 'assigned' : 'finding_staff';

        DonDat::create([
            'ID_DD'          => $idDon,
            'LoaiDon'        => $validated['loai_don'],
            'ID_DV'          => $validated['id_dv'],
            'ID_KH'          => $customer->ID_KH,
            'ID_DC'          => $idDc,
            'GhiChu'         => $validated['ghi_chu'] ?? null,
            'NgayLam'        => $validated['ngay_lam'] ?? null,
            'GioBatDau'      => $gioBatDau ? $gioBatDau . ':00' : null,
            'ThoiLuongGio'   => $validated['thoi_luong'] ?? null,
            'ID_Goi'         => null,
            'NgayBatDauGoi'  => null,
            'NgayKetThucGoi' => null,
            'TrangThaiDon'   => $trangThaiDon,
            'TongTien'       => $tongTien,
            'TongTienSauGiam'=> $tongSauGiam,
            'ID_NV'          => $validated['id_nv'],
        ]);

        // Luu chi tiet khuyen mai (ho tro 1 hoac nhieu voucher cho 1 don)
        $voucherRows = [];

        // Neu front-end gui danh sach vouchers chi tiet thi luu theo danh sach nay
        if (!empty($appliedVouchers)) {
            foreach ($appliedVouchers as $voucher) {
                // Chap nhan ca dang string (chi ma) va dang array (co id_km, tien_giam)
                if (is_string($voucher)) {
                    $voucherRows[] = [
                        'id_km'     => $voucher,
                        'tien_giam' => 0.0,
                    ];
                } elseif (is_array($voucher) && !empty($voucher['id_km'])) {
                    $voucherRows[] = [
                        'id_km'     => $voucher['id_km'],
                        'tien_giam' => isset($voucher['tien_giam'])
                            ? (float) $voucher['tien_giam']
                            : 0.0,
                    ];
                }
            }
        } elseif (!empty($singleVoucher)) {
            // Truong hop chi co 1 ma (id_km) va khong co mang vouchers: luu duy nhat 1 dong, tien giam = tong giam
            $discountTotal = max(0, $tongTien - $tongSauGiam);
            $voucherRows[] = [
                'id_km'     => $singleVoucher,
                'tien_giam' => $discountTotal,
            ];
        }

        foreach ($voucherRows as $row) {
            ChiTietKhuyenMai::create([
                'ID_DD'    => $idDon,
                'ID_KM'    => $row['id_km'],
                'TienGiam' => $row['tien_giam'],
            ]);
        }

        $paymentUrl = null;

        if ($paymentMethod === 'vnpay') {
            $paymentUrl = $vnPay->createPaymentUrl([
                'txn_ref'    => $idDon,
                'amount'     => $tongSauGiam,
                'order_info' => 'Thanh toan don dat ' . $idDon,
            ]);

            LichSuThanhToan::create([
                'ID_LSTT'             => IdGenerator::next('LichSuThanhToan', 'ID_LSTT', 'LSTT_'),
                'PhuongThucThanhToan' => 'VNPay',
                'TrangThai'           => 'ChoXuLy',
                'SoTienThanhToan'     => $tongSauGiam,
                'MaGiaoDichVNPAY'     => null,
                'ID_DD'               => $idDon,
            ]);
        } else {
            LichSuThanhToan::create([
                'ID_LSTT'             => IdGenerator::next('LichSuThanhToan', 'ID_LSTT', 'LSTT_'),
                'PhuongThucThanhToan' => 'TienMat',
                'TrangThai'           => 'ThanhCong',
                'SoTienThanhToan'     => $tongSauGiam,
                'MaGiaoDichVNPAY'     => null,
                'ID_DD'               => $idDon,
            ]);
        }

        $response = [
            'success' => true,
            'id_dd'   => $idDon,
        ];

        if ($paymentUrl !== null) {
            $response['payment_url'] = $paymentUrl;
        }

        return response()->json($response);
    }

    public function vnpayReturn(Request $request)
    {
        $vnp_SecureHash = $request->query('vnp_SecureHash');
        $inputData      = [];
        foreach ($request->query() as $key => $value) {
            if (str_starts_with($key, 'vnp_') && $key !== 'vnp_SecureHash') {
                $inputData[$key] = $value;
            }
        }

        ksort($inputData);

        $hashData = [];
        foreach ($inputData as $key => $value) {
            $hashData[] = urlencode($key) . '=' . urlencode((string) $value);
        }

        $hashSecret = config('vnpay.hash_secret');
        $secureHash = hash_hmac('sha512', implode('&', $hashData), $hashSecret);

        $isValidSignature = hash_equals($secureHash, (string) $vnp_SecureHash);

        $txnRef        = $request->query('vnp_TxnRef');
        $responseCode  = $request->query('vnp_ResponseCode');
        $transactionNo = $request->query('vnp_TransactionNo');

        $status  = 'failed';
        $message = 'Thanh toan that bai.';

        if ($isValidSignature && $responseCode === '00') {
            $status  = 'success';
            $message = 'Thanh toan thanh cong.';

            if ($txnRef) {
                $order = DonDat::find($txnRef);
                if ($order) {
                    if ($order->ID_NV) {
                        $order->TrangThaiDon = 'assigned';
                    } else {
                        $order->TrangThaiDon = 'finding_staff';
                    }
                    $order->save();

                    $payment = LichSuThanhToan::where('ID_DD', $order->ID_DD)
                        ->where('PhuongThucThanhToan', 'VNPay')
                        ->orderByDesc('ThoiGian')
                        ->first();

                    if ($payment) {
                        $payment->TrangThai = 'ThanhCong';
                        $payment->MaGiaoDichVNPAY = $transactionNo;
                        $payment->save();
                    }
                }
            }
        } elseif (!$isValidSignature) {
            $message = 'Chu ky khong hop le.';
        }

        return view('payment-result', [
            'status'        => $status,
            'message'       => $message,
            'orderId'       => $txnRef,
            'transactionNo' => $transactionNo,
            'responseCode'  => $responseCode,
        ]);
    }

    private function voucherUsedByCustomer(string $customerId, string $code): bool
    {
        return DonDat::where('ID_KH', $customerId)
            ->whereExists(function ($sub) use ($code) {
                $sub->selectRaw('1')
                    ->from('ChiTietKhuyenMai')
                    ->whereColumn('ChiTietKhuyenMai.ID_DD', 'DonDat.ID_DD')
                    ->where('ChiTietKhuyenMai.ID_KM', $code);
            })
            ->exists();
    }

    private function distanceKm(float $lat1, float $lon1, float $lat2, float $lon2): float
    {
        $R = 6371;
        $dLat = deg2rad($lat2 - $lat1);
        $dLon = deg2rad($lon2 - $lon1);

        $a = sin($dLat / 2) ** 2
            + cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * sin($dLon / 2) ** 2;

        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));

        return $R * $c;
    }

    private function guessQuanFromAddress(string $address): ?Quan
    {
        $address = trim($address);
        if ($address === '') {
            return null;
        }

        $segments = array_map('trim', explode(',', $address));
        $candidate = null;

        if (count($segments) >= 3) {
            // Vi du: 140 Le Trong Tan, Tay Thanh, Tan Phu, Thanh pho Ho Chi Minh
            // -> Quan/huyen thuong nam o vi tri thu 2 tu cuoi len.
            $candidate = $segments[count($segments) - 2];
        } elseif (count($segments) >= 2) {
            $candidate = $segments[1];
        } else {
            $candidate = $address;
        }

        $candidate = trim((string) $candidate);
        if ($candidate === '') {
            return null;
        }

        // Tim quan/huyen bang LIKE truoc
        $quan = Quan::where('TenQuan', 'like', '%' . $candidate . '%')->first();
        if ($quan) {
            return $quan;
        }

        // Thu loai bo cac tien to thong dung: Quan, Huyen, TP, Thanh pho...
        $normalize = static function (string $value): string {
            $value = preg_replace('/^(Quan|Huyen|TP\\.?|Thanh pho)\\s+/iu', '', $value);
            return trim((string) $value);
        };

        $normalizedCandidate = $normalize($candidate);
        if ($normalizedCandidate === '') {
            return null;
        }

        $quans = Quan::all();

        foreach ($quans as $quanItem) {
            if (!$quanItem->TenQuan) {
                continue;
            }

            $normalizedTenQuan = $normalize($quanItem->TenQuan);
            if ($normalizedTenQuan !== '' &&
                mb_stripos($normalizedCandidate, $normalizedTenQuan) !== false) {
                return $quanItem;
            }

            if (mb_stripos($address, $quanItem->TenQuan) !== false) {
                return $quanItem;
            }
        }

        return null;
    }
}
