<?php

namespace App\Http\Controllers;

use App\Models\DanhGiaNhanVien;
use App\Models\DiaChi;
use App\Models\DichVu;
use App\Models\DonDat;
use App\Models\ChiTietKhuyenMai;
use App\Models\KhuyenMai;
use App\Models\LichBuoiThang;
use App\Models\GoiThang;
use App\Models\LichLamViec;
use App\Models\LichSuThanhToan;
use App\Models\LichTheoTuan;
use App\Models\Quan;
use App\Models\NhanVien;
use App\Models\User;
use App\Services\VNPayService;
use App\Support\IdGenerator;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

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

        // Filter out staff who have conflicting bookings
        $availableStaffIds = [];
        foreach ($lich as $item) {
            $nv = $item->nhanVien;
            if (!$nv) {
                continue;
            }

            // Check if this staff has any conflicting bookings on the same day
            $hasConflict = $this->hasTimeConflict($nv->ID_NV, $ngayLam, $gioBatDauSql, $gioKetThuc);
            
            if (!$hasConflict) {
                $availableStaffIds[$nv->ID_NV] = $item;
            }
        }

        $results = [];

        foreach ($availableStaffIds as $nvId => $item) {
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

            // Xử lý ảnh nhân viên: nếu null hoặc rỗng, dùng ảnh mặc định
            $hinhAnh = $nv->HinhAnh;
            if (empty($hinhAnh)) {
                // Ảnh mặc định nếu nhân viên chưa có ảnh
                $hinhAnh = 'https://ui-avatars.com/api/?name=' . urlencode($nv->Ten_NV) . '&background=004d2e&color=fff&size=150';
            } elseif (!str_starts_with($hinhAnh, 'http')) {
                // Nếu là đường dẫn tương đối, thêm base URL
                $hinhAnh = url('storage/' . ltrim($hinhAnh, '/'));
            }

            $results[] = [
                'id_nv'             => $nv->ID_NV,
                'ten_nv'            => $nv->Ten_NV,
                'hinh_anh'          => $hinhAnh,
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
            'gio_bat_dau'    => ['required_if:loai_don,month', 'date_format:H:i'],
            'thoi_luong'     => ['nullable', 'integer', 'min:1'],
            'tong_tien'      => ['required', 'numeric', 'min:0'],
            'tong_sau_giam'  => ['nullable', 'numeric', 'min:0'],
            'id_nv'          => ['nullable', 'string'],
            'id_km'          => ['nullable', 'string'],
            'repeat_days'    => ['required_if:loai_don,month', 'array', 'min:1'],
            'repeat_days.*'  => ['integer', 'between:0,6'],
            'repeat_start_date' => ['nullable', 'date'],
            'repeat_end_date'   => ['nullable', 'date'],
            'package_months' => ['required_if:loai_don,month', 'integer', 'in:1,2,3,6'],
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

        $packageMonths = null;
        $repeatDays = [];
        $startDate = null;
        $endDateExclusive = null;
        $idGoi = null;

        if ($validated['loai_don'] === 'month') {
            $packageMonths = (int) ($validated['package_months'] ?? 0);
            $repeatDays = array_values(array_unique(array_map('intval', $validated['repeat_days'] ?? [])));

            if (empty($repeatDays)) {
                return response()->json(['error' => 'Vui long chon thu lap lai.'], 422);
            }

            $goiMap = [
                1 => 'GT01',
                2 => 'GT02',
                3 => 'GT03',
                6 => 'GT04',
            ];

            $idGoi = $goiMap[$packageMonths] ?? null;
            if ($idGoi === null) {
                $idGoi = GoiThang::where('SoNgay', $packageMonths * 30)->value('ID_Goi');
            }

            if ($idGoi === null) {
                return response()->json(['error' => 'Goi thang khong hop le.'], 422);
            }

            $defaultSoNgay = $packageMonths > 0 ? $packageMonths * 30 : 0;
            $soNgayDb = GoiThang::where('ID_Goi', $idGoi)->value('SoNgay');
            $soNgay = $soNgayDb ?: $defaultSoNgay;
            if ($soNgay <= 0) {
                return response()->json(['error' => 'So ngay hieu luc goi khong hop le.'], 422);
            }
            if ($soNgay <= 0) {
                return response()->json(['error' => 'So ngay hieu luc goi khong hop le.'], 422);
            }

            $orderDate = Carbon::now();
            $startDate = $this->computePackageStartDate($orderDate, $repeatDays);
            $endDateExclusive = $startDate->copy()->addDays($soNgay);
        }

        $service = DichVu::findOrFail($validated['id_dv']);

        if ($validated['loai_don'] === 'month') {
            $basePrice = (float) $service->GiaDV;
            $package = $idGoi ? GoiThang::find($idGoi) : null;
            $packagePercent = $package && $package->PhanTramGiam !== null
                ? (float) $package->PhanTramGiam
                : 0.0;

            $sessionCount = $this->countSessionsInRange($repeatDays, $startDate, $endDateExclusive);
            $gross = $basePrice * $sessionCount;
            $packageDiscount = $gross * $packagePercent / 100;
            $tongTien = max(0, $gross - $packageDiscount);
        } else {
            $tongTien = (float) $validated['tong_tien'];
        }

        $tongSauGiam = isset($validated['tong_sau_giam']) && $validated['tong_sau_giam'] !== null
            ? (float) $validated['tong_sau_giam']
            : $tongTien;

        $gioBatDauRaw = $validated['gio_bat_dau'] ?? null;
        $gioBatDau = $gioBatDauRaw ? $gioBatDauRaw . ':00' : null;
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

        $booking = DonDat::create([
            'ID_DD'          => $idDon,
            'LoaiDon'        => $validated['loai_don'],
            'ID_DV'          => $validated['id_dv'],
            'ID_KH'          => $customer->ID_KH,
            'ID_DC'          => $idDc,
            'GhiChu'         => $validated['ghi_chu'] ?? null,
            'NgayLam'        => $validated['loai_don'] === 'hour' ? ($validated['ngay_lam'] ?? null) : null,
            'GioBatDau'      => $gioBatDau,
            'ThoiLuongGio'   => $validated['thoi_luong'] ?? null,
            'ID_Goi'         => $validated['loai_don'] === 'month' ? $idGoi : null,
            'NgayBatDauGoi'  => $validated['loai_don'] === 'month' ? $startDate?->toDateString() : null,
            'NgayKetThucGoi' => $validated['loai_don'] === 'month'
                ? $endDateExclusive?->copy()->subDay()->toDateString()
                : null,
            'TrangThaiDon'   => $trangThaiDon,
            'TongTien'       => $tongTien,
            'TongTienSauGiam'=> $tongSauGiam,
            'ID_NV'          => $validated['id_nv'],
        ]);

        if ($booking->TrangThaiDon === 'assigned' && $booking->ID_NV) {
            $this->notifyStaffAssigned($booking);
        }

        if ($validated['loai_don'] === 'month') {
            foreach ($repeatDays as $day) {
                LichTheoTuan::create([
                    'ID_LichTuan' => IdGenerator::next('LichTheoTuan', 'ID_LichTuan', 'LTT'),
                    'ID_DD'       => $idDon,
                    'Thu'         => $day,
                    'GioBatDau'   => $gioBatDau,
                ]);
            }

            if ($startDate && $endDateExclusive) {
                $cursor = $startDate->copy();
                $endExclusive = $endDateExclusive->copy();
                while ($cursor->lt($endExclusive)) {
                    if (in_array($cursor->dayOfWeek, $repeatDays, true)) {
                        LichBuoiThang::create([
                            'ID_Buoi'      => IdGenerator::next('LichBuoiThang', 'ID_Buoi', 'LBT_'),
                            'ID_DD'        => $idDon,
                            'NgayLam'      => $cursor->toDateString(),
                            'GioBatDau'    => $gioBatDau,
                            'TrangThaiBuoi'=> 'scheduled',
                            'ID_NV'        => null,
                        ]);
                    }
                    $cursor->addDay();
                }
            }
        }

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

                    if ($order->TrangThaiDon === 'assigned' && $order->ID_NV) {
                        $this->notifyStaffAssigned($order);
                    }

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

    private function notifyStaffAssigned(DonDat $booking): void
    {
        try {
            $nhanVien = NhanVien::where('ID_NV', $booking->ID_NV)->first();
            if (!$nhanVien || !$nhanVien->ID_TK) {
                return;
            }

            $user = User::where('id_tk', $nhanVien->ID_TK)->first();
            if (!$user) {
                return;
            }

            $service = DichVu::find($booking->ID_DV);
            $title = 'Don moi duoc gan';
            $body = 'Don ' . $booking->ID_DD . ' - Dich vu ' . ($service?->TenDV ?? '');
            $this->sendOneSignalToUser($user, $title, $body, $booking);
        } catch (\Exception $e) {
            // Không làm gián đoạn flow đặt đơn nếu gửi thông báo lỗi
        }
    }

    private function sendOneSignalToUser(User $user, string $title, string $body, DonDat $booking): void
    {
        $appId = config('services.onesignal.app_id');
        $apiKey = config('services.onesignal.api_key');
        if (!$appId || !$apiKey) {
            return;
        }

        // Debug logging
        Log::info('OneSignal Debug', [
            'app_id' => $appId,
            'api_key_length' => strlen($apiKey),
            'api_key_prefix' => substr($apiKey, 0, 20) . '...',
            'user_onesignal_player_id' => $user->onesignal_player_id,
        ]);

        $payload = [
            'app_id' => $appId,
            'include_external_user_ids' => [(string)$user->id],
            'channel_for_external_user_ids' => 'push',
            'include_player_ids' => array_filter([$user->onesignal_player_id]),
            'headings' => ['en' => $title],
            'contents' => ['en' => $body],
            'data' => [
                'booking_id' => $booking->ID_DD,
                'type' => 'new_booking',
            ],
        ];

        $response = Http::withHeaders([
            'Authorization' => 'Key ' . $apiKey,
            'Content-Type' => 'application/json',
        ])->post('https://api.onesignal.com/notifications?c=push', $payload);

        if ($response->failed()) {
            Log::warning('OneSignal send failed (web booking)', [
                'user_id' => $user->id,
                'status' => $response->status(),
                'body' => $response->body(),
            ]);
        }
    }

    /**
     * Check if a staff member has any time conflicts with existing bookings
     */
    private function hasTimeConflict(string $staffId, string $date, string $startTime, string $endTime): bool
    {
        // Calculate end time based on start time and duration
        // Check for bookings that overlap with the requested time range
        return DonDat::where('ID_NV', $staffId)
            ->where('NgayLam', $date)
            ->whereNotIn('TrangThaiDon', ['canceled', 'rejected']) // Exclude canceled and rejected bookings
            ->where(function ($query) use ($startTime, $endTime) {
                // Check for time overlap using SQL time comparison
                // Two time ranges overlap if: (StartA < EndB) AND (EndA > StartB)
                $query->whereRaw('GioBatDau < ?', [$endTime])
                      ->whereRaw('ADDTIME(GioBatDau, SEC_TO_TIME(ThoiLuongGio * 3600)) > ?', [$startTime]);
            })
            ->exists();
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

    private function countSessionsInRange(array $weekdays, $startDate, $endDateExclusive): int
    {
        $normalizedDays = array_unique(array_map('intval', $weekdays));
        sort($normalizedDays);

        $start = $startDate instanceof Carbon ? $startDate->copy()->startOfDay() : Carbon::parse($startDate)->startOfDay();
        $endExclusive = $endDateExclusive instanceof Carbon ? $endDateExclusive->copy()->startOfDay() : Carbon::parse($endDateExclusive)->startOfDay();

        $count = 0;
        $cursor = $start->copy();

        while ($cursor->lt($endExclusive)) {
            if (in_array((int) $cursor->dayOfWeek, $normalizedDays, true)) {
                $count++;
            }
            $cursor->addDay();
        }

        return $count;
    }

    private function computePackageStartDate(Carbon $orderDate, array $weekdays): Carbon
    {
        $normalizedDays = array_values(array_unique(array_map('intval', $weekdays)));
        $normalizedDays = array_filter($normalizedDays, static fn ($d) => $d >= 0 && $d <= 6);

        // Bo qua 2 ngay ngay sau ngay dat, bat dau tu ngay thu 3 (khong can trung thu da chon)
        return $orderDate->copy()->addDays(3)->startOfDay();
    }
}
