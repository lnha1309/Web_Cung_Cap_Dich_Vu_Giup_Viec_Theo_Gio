<?php

namespace App\Http\Controllers;

use App\Models\DanhGiaNhanVien;
use App\Models\DiaChi;
use App\Models\DichVu;
use App\Models\DonDat;
use App\Models\ChiTietKhuyenMai;
use App\Models\ChiTietPhuThu;
use App\Models\KhuyenMai;
use App\Models\LichBuoiThang;
use App\Models\GoiThang;
use App\Models\LichLamViec;
use App\Models\LichSuThanhToan;
use App\Models\LichTheoTuan;
use App\Models\PhuThu;
use App\Models\Quan;
use App\Models\NhanVien;
use App\Models\TaiKhoan;
use App\Services\VNPayService;
use App\Services\SurchargeService;
use App\Services\StaffWalletService;
use App\Services\RefundService;
use App\Services\NotificationService;
use App\Support\IdGenerator;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
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
        $services = DichVu::where('is_delete', false)
            ->orderBy('ThoiLuong')
            ->get([
                'ID_DV',
                'TenDV',
                'MoTa',
                'GiaDV',
                'DienTichToiDa',
                'SoPhong',
                'ThoiLuong',
            ]);

        // Load gói tháng chưa bị xoá mềm
        $monthlyPackages = GoiThang::where('is_delete', false)
            ->orderBy('SoNgay')
            ->get([
                'ID_Goi',
                'TenGoi',
                'SoNgay',
                'PhanTramGiam',
            ]);

        // Load phụ thu thú cưng (PT002) - chỉ phụ thu này cho người dùng chọn
        // Các phụ thu khác (giờ cao điểm, cuối tuần) tính tự động phía backend
        $surcharges = PhuThu::where('is_delete', false)
            ->where('ID_PT', 'PT002')
            ->get([
                'ID_PT',
                'Ten_PT',
                'GiaCuoc',
            ]);

        return view('booking', [
            'selectedAddress' => $addressText,
            'services' => $services,
            'monthlyPackages' => $monthlyPackages,
            'surcharges' => $surcharges,
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
            return response()->json(['error' => 'Thời lượng không hợp lệ'], 422);
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
                ->where('TrangThaiDon', 'completed')
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

    /**
     * Get surcharge prices (API endpoint)
     * Trả về 0 nếu phụ thu bị xoá mềm (is_delete = true)
     */
    public function getSurcharges()
    {
        $surcharges = PhuThu::where('is_delete', false)
            ->whereIn('ID_PT', ['PT001', 'PT002', 'PT003'])
            ->get()
            ->keyBy('ID_PT');

        return response()->json([
            'PT001' => $surcharges->get('PT001')?->GiaCuoc ?? 0,
            'PT002' => $surcharges->get('PT002')?->GiaCuoc ?? 0,
            'PT003' => $surcharges->get('PT003')?->GiaCuoc ?? 0,
        ]);
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
            return response()->json(['error' => 'Vui lòng đăng nhập trước khi áp dụng mã khuyến mãi.'], 403);
        }

        $km = KhuyenMai::where('ID_KM', $code)
            ->where('TrangThai', 'activated')
            ->where('is_delete', false)
            ->first();

        if (!$km) {
            return response()->json(['error' => 'Mã khuyến mãi không hộp lệ hoặc đã ngừng hoạt động.'], 422);
        }

        $today = Carbon::today();
        if (($km->NgayBatDau && $today->lt(Carbon::parse($km->NgayBatDau))) ||
            ($km->NgayKetThuc && $today->gt(Carbon::parse($km->NgayKetThuc)))) {
            return response()->json(['error' => 'Mã khuyến mãi đã hết hạn.'], 422);
        }

        // Khong cho khach hang ap lai ma da dung truoc do
        if ($this->voucherUsedByCustomer($customer->ID_KH, $code)) {
            return response()->json(['error' => 'Mã khuyến mãi này đã được sử dụng trước đó nên không thể áp dụng lại.'], 422);
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

    public function confirm(Request $request, VNPayService $vnPay, SurchargeService $surchargeService)
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
            'has_pets'      => ['nullable', 'boolean'],
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

        $sessionCount = 1;
        $weekendSessionCount = 0;

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
            $weekendDays = array_intersect($repeatDays, [0, 6]);
            $weekendSessionCount = empty($weekendDays)
                ? 0
                : $this->countSessionsInRange($weekendDays, $startDate, $endDateExclusive);

            $gross = $basePrice * $sessionCount;
            $packageDiscount = $gross * $packagePercent / 100;
            $tongTien = max(0, $gross - $packageDiscount);
        } else {
            $tongTien = (float) $validated['tong_tien'];
        }

        $tongSauGiam = isset($validated['tong_sau_giam']) && $validated['tong_sau_giam'] !== null
            ? (float) $validated['tong_sau_giam']
            : $tongTien;

        // Tinh giam gia tu voucher (ap dung tren tong truoc phu thu)
        $baseSubtotal = $tongTien;
        $voucherRows = [];
        $discountTotal = 0.0;

        $resolveDiscount = static function (string $code, ?float $providedAmount) use ($baseSubtotal): float {
            $amount = $providedAmount;

            if ($amount === null) {
                $km = KhuyenMai::where('ID_KM', $code)
                    ->where('TrangThai', 'activated')
                    ->where('is_delete', false)
                    ->first();

                if ($km) {
                    $amount = $baseSubtotal * ((float) $km->PhanTramGiam / 100);
                    if ($km->GiamToiDa !== null) {
                        $amount = min($amount, (float) $km->GiamToiDa);
                    }
                } else {
                    $amount = 0.0;
                }
            }

            return max(0.0, (float) $amount);
        };

        if (!empty($appliedVouchers)) {
            foreach ($appliedVouchers as $voucher) {
                $code = $voucher['id_km'] ?? null;
                if (!$code) {
                    continue;
                }

                $provided = isset($voucher['tien_giam']) && is_numeric($voucher['tien_giam'])
                    ? (float) $voucher['tien_giam']
                    : null;

                $amount = $resolveDiscount($code, $provided);
                $voucherRows[] = [
                    'id_km'     => $code,
                    'tien_giam' => $amount,
                ];
                $discountTotal += $amount;
            }
        } elseif (!empty($singleVoucher)) {
            $amount = $resolveDiscount($singleVoucher, null);
            $voucherRows[] = [
                'id_km'     => $singleVoucher,
                'tien_giam' => $amount,
            ];
            $discountTotal += $amount;
        }

        // Khong de tong giam lon hon gia tri truoc phu thu
        if ($discountTotal > $baseSubtotal && !empty($voucherRows)) {
            $excess = $discountTotal - $baseSubtotal;
            $lastIndex = count($voucherRows) - 1;
            $voucherRows[$lastIndex]['tien_giam'] = max(
                0.0,
                $voucherRows[$lastIndex]['tien_giam'] - $excess
            );
            $discountTotal = array_sum(array_column($voucherRows, 'tien_giam'));
        }

        if ($discountTotal > 0) {
            $tongSauGiam = max(0, $baseSubtotal - $discountTotal);
        }

        $gioBatDauRaw = $validated['gio_bat_dau'] ?? null;
        $gioBatDau = $gioBatDauRaw ? $gioBatDauRaw . ':00' : null;
        $selectedStaffId = $validated['id_nv'] ?? null;
        $hasPets = array_key_exists('has_pets', $validated)
            ? (bool) $validated['has_pets']
            : false;

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

        $ngayLam = $validated['loai_don'] === 'hour'
            ? ($validated['ngay_lam'] ?? null)
            : null;
        $repeatDaysForSurcharge = $validated['loai_don'] === 'month' ? $repeatDays : [];
        if ($validated['loai_don'] === 'hour' && $ngayLam && $this->countSessionsInRange([0, 6], $ngayLam, Carbon::parse($ngayLam)->copy()->addDay())) {
            $weekendSessionCount = 1;
        }

        $surchargeResult = $surchargeService->calculate(
            $validated['loai_don'],
            $ngayLam,
            $gioBatDau,
            $repeatDaysForSurcharge,
            $hasPets,
            $sessionCount,
            $weekendSessionCount
        );

        // Add surcharges to total
        $tongTien += $surchargeResult['total'];
        $tongSauGiam += $surchargeResult['total'];

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
                            'TrangThaiBuoi'=> 'finding_staff',
                            'ID_NV'        => null,
                        ]);
                    }
                    $cursor->addDay();
                }
            }
        }

        // Insert surcharge records into ChiTietPhuThu
        foreach ($surchargeResult['items'] as $item) {
            ChiTietPhuThu::create([
                'ID_PT'  => $item['id'],
                'ID_DD'  => $idDon,
                'Ghichu' => $item['note'] . ' - ' . number_format($item['unit_amount']) . ' x ' . $item['quantity'],
            ]);
        }

        // Luu chi tiet khuyen mai (ho tro 1 hoac nhieu voucher cho 1 don)
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

        // Send notification to customer about order creation
        // CHỈ gửi thông báo cho đơn thanh toán tiền mặt ngay lập tức
        // Với VNPay, sẽ gửi thông báo sau khi thanh toán thành công (trong vnpayReturn)
        if ($paymentMethod !== 'vnpay') {
            try {
                $notificationService = app(NotificationService::class);
                // Reload booking with relationships for notification
                $bookingForNotification = DonDat::with('dichVu')->find($idDon);
                if ($bookingForNotification) {
                    $notificationService->notifyOrderCreated($bookingForNotification);
                }
            } catch (\Exception $e) {
                Log::error('Failed to send order creation notification', [
                    'booking_id' => $idDon,
                    'error' => $e->getMessage(),
                ]);
            }
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
        Log::info('VNPay return callback', [
            'query' => $request->query(),
            'ip'    => $request->ip(),
        ]);

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
        $message = 'Thanh toán thất bại.';
        $handledOrder = false;
        $handledWallet = false;

        if (!$isValidSignature) {
            $message = 'Chữ ký không hợp lệ.';
        }

        if ($isValidSignature && $responseCode === '00') {
            $status  = 'success';
            $message = 'Thanh toán thành công.';

            if ($txnRef) {
                $order = DonDat::find($txnRef);
                if ($order) {
                    $handledOrder = true;
                    // Save old status for notification
                    $oldStatus = $order->TrangThaiDon;
                    
                    if ($order->ID_NV) {
                        $order->TrangThaiDon = 'assigned';
                    } else {
                        $order->TrangThaiDon = 'finding_staff';
                    }
                    $newStatus = $order->TrangThaiDon;
                    $order->save();

                    if ($order->TrangThaiDon === 'assigned' && $order->ID_NV) {
                        $this->notifyStaffAssigned($order);
                    }

                    // Send order creation notification to customer (cho đơn VNPay)
                    try {
                        $notificationService = app(NotificationService::class);
                        // Reload booking with relationships for notification
                        $bookingForNotification = DonDat::with('dichVu')->find($order->ID_DD);
                        if ($bookingForNotification) {
                            $notificationService->notifyOrderCreated($bookingForNotification);
                        }
                    } catch (\Exception $e) {
                        Log::error('Failed to send order creation notification after VNPay success', [
                            'booking_id' => $order->ID_DD,
                            'error' => $e->getMessage(),
                        ]);
                    }

                    // Send status change notification to customer
                    if ($oldStatus !== $newStatus) {
                        try {
                            $notificationService = app(NotificationService::class);
                            $notificationService->notifyOrderStatusChanged($order, $oldStatus, $newStatus);
                        } catch (\Exception $e) {
                            Log::error('Failed to send status change notification', [
                                'booking_id' => $order->ID_DD,
                                'old_status' => $oldStatus,
                                'new_status' => $newStatus,
                                'error' => $e->getMessage(),
                            ]);
                        }
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
                } else {
                    $paymentRecord = LichSuThanhToan::find($txnRef);
                    if ($paymentRecord && $paymentRecord->ID_DD) {
                        $handledOrder = true;
                        $paymentRecord->TrangThai = 'ThanhCong';
                        $paymentRecord->MaGiaoDichVNPAY = $transactionNo;
                        $paymentRecord->save();
                        
                        // ===========================================
                        // Xử lý reschedule_surcharge
                        // ===========================================
                        if ($paymentRecord->LoaiGiaoDich === 'reschedule_surcharge') {
                            $message = 'Thanh toan phu thu thanh cong.';
                            
                            $booking = DonDat::find($paymentRecord->ID_DD);
                            if ($booking) {
                                // GhiChu format: "Phu thu doi gio cao diem|{json}"
                                $ghiChuParts = explode('|', $paymentRecord->GhiChu ?? '', 2);
                                if (count($ghiChuParts) >= 2) {
                                    $rescheduleInfo = json_decode($ghiChuParts[1], true);
                                    if (is_array($rescheduleInfo)) {
                                        // Cập nhật đơn với giờ mới
                                        $booking->NgayLam = $rescheduleInfo['new_date'] ?? $booking->NgayLam;
                                        $booking->GioBatDau = ($rescheduleInfo['new_time'] ?? '') . ':00';
                                        $booking->FindingStaffResponse = 'reschedule';
                                        $booking->RescheduleCount = ($booking->RescheduleCount ?? 0) + 1;
                                        
                                        // Thêm phụ thu vào tổng tiền
                                        $surchargeAmount = $paymentRecord->SoTienThanhToan ?? 30000;
                                        $booking->TongTien = ($booking->TongTien ?? 0) + $surchargeAmount;
                                        $booking->TongTienSauGiam = ($booking->TongTienSauGiam ?? $booking->TongTien ?? 0) + $surchargeAmount;
                                        $booking->save();
                                        
                                        // Tạo record phụ thu
                                        ChiTietPhuThu::firstOrCreate([
                                            'ID_PT' => 'PT001',
                                            'ID_DD' => $booking->ID_DD,
                                        ], [
                                            'Ghichu' => 'Phụ thu đổi giờ cao điểm (trước 8h hoặc 17h)',
                                        ]);
                                        
                                        Log::info('Reschedule surcharge payment success - order updated', [
                                            'booking_id' => $booking->ID_DD,
                                            'new_date' => $booking->NgayLam,
                                            'new_time' => $booking->GioBatDau,
                                        ]);
                                    }
                                }
                                
                                try {
                                    $notificationService = app(NotificationService::class);
                                    $notificationService->notifyOrderRescheduled($booking);
                                } catch (\Exception $e) {
                                    Log::error('Failed to send reschedule notification after surcharge success', [
                                        'booking_id' => $booking->ID_DD,
                                        'error' => $e->getMessage(),
                                    ]);
                                }
                            }
                            
                            // Redirect to booking detail page after surcharge payment
                            $appRedirect = $request->query('app_redirect');
                            if ($appRedirect) {
                                return redirect()->away($appRedirect . '?booking_id=' . $paymentRecord->ID_DD . '&status=success');
                            }
                            return redirect()->route('bookings.detail', $paymentRecord->ID_DD)
                                ->with('success', 'Đã thanh toán phụ thu thành công. Đơn hàng đã được cập nhật.');
                        }
                } else {
                        $walletTx = app(StaffWalletService::class)
                            ->finalizeTopup($txnRef, true, $transactionNo, $responseCode);
                        if ($walletTx) {
                            $handledWallet = true;
                            $message = 'Nap tien thanh cong.';
                        }
                    }
                }
            }
        } elseif ($isValidSignature && $txnRef) {
            // Mark payment as failed when VNPay returns non-00
            $order = DonDat::with('dichVu', 'khachHang')->find($txnRef);
            if ($order) {
                $handledOrder = true;
                
                // Đánh dấu payment là thất bại
                $payment = LichSuThanhToan::where('ID_DD', $order->ID_DD)
                    ->where('PhuongThucThanhToan', 'VNPay')
                    ->orderByDesc('ThoiGian')
                    ->first();
                if ($payment) {
                    $payment->TrangThai = 'ThatBai';
                    $payment->MaGiaoDichVNPAY = $transactionNo;
                    $payment->save();
                }
                
                // Đổi trạng thái đơn thành cancelled
                $order->TrangThaiDon = 'cancelled';
                $order->save();
                
                // Xác định lý do hủy
                $cancelType = $responseCode === '24' ? 'user_cancel' : 'payment_failed';
                
                // Gửi notification cho khách hàng
                try {
                    $notificationService = app(NotificationService::class);
                    $notificationService->notifyOrderCancelled($order, $cancelType, [
                        'payment_method' => 'VNPay',
                        'amount' => 0, // Không có hoàn tiền vì chưa thanh toán
                    ]);
                } catch (\Exception $e) {
                    Log::error('Failed to send cancellation notification', [
                        'order_id' => $order->ID_DD,
                        'error' => $e->getMessage(),
                    ]);
                }
                
                Log::info('Order cancelled due to VNPay payment failure', [
                    'order_id' => $txnRef,
                    'response_code' => $responseCode,
                    'cancel_type' => $cancelType,
                ]);
                
                $message = $responseCode === '24' 
                    ? 'Đã huỷ thanh toán' 
                    : 'Thanh toán không thành công. Mã: ' . $responseCode;
            } else {
                $paymentRecord = LichSuThanhToan::find($txnRef);
                if ($paymentRecord && $paymentRecord->ID_DD) {
                    $handledOrder = true;
                    $paymentRecord->TrangThai = 'ThatBai';
                    $paymentRecord->MaGiaoDichVNPAY = $transactionNo;
                    $paymentRecord->save();
                    $message = 'Thanh toan phu phi that bai, don chua duoc cap nhat';

                    $booking = DonDat::find($paymentRecord->ID_DD);
                    $meta = json_decode($paymentRecord->GhiChu ?? '', true);
                    if ($booking && is_array($meta) && ($meta['type'] ?? null) === 'reschedule_surcharge') {
                        $booking->NgayLam = $meta['old_date'] ?? $booking->NgayLam;
                        $booking->GioBatDau = $meta['old_time'] ?? $booking->GioBatDau;
                        $booking->RescheduleCount = max(0, ($booking->RescheduleCount ?? 1) - 1);
                        $booking->FindingStaffResponse = null;
                        $booking->save();

                        ChiTietPhuThu::where('ID_DD', $booking->ID_DD)
                            ->whereIn('ID_PT', ['PT001', 'PT003'])
                            ->delete();
                    }

                    $appRedirect = $request->query('app_redirect');
                    if ($appRedirect) {
                        return redirect()->away($appRedirect . '?booking_id=' . $paymentRecord->ID_DD . '&status=failed');
                    }
                    return redirect()->route('bookings.detail', $paymentRecord->ID_DD)
                        ->with('error', $message);
                }
            }
        }

        if ($txnRef && !$handledOrder && (!$isValidSignature || $responseCode !== '00')) {
            $order = DonDat::find($txnRef);
            if ($order) {
                $handledOrder = true;
                $payment = LichSuThanhToan::where('ID_DD', $order->ID_DD)
                    ->where('PhuongThucThanhToan', 'VNPay')
                    ->orderByDesc('ThoiGian')
                    ->first();
                if ($payment) {
                    $payment->TrangThai = 'ThatBai';
                    $payment->MaGiaoDichVNPAY = $transactionNo;
                    $payment->save();
                }
            } elseif (($paymentRecord = LichSuThanhToan::find($txnRef)) && $paymentRecord->ID_DD) {
                $handledOrder = true;
                $paymentRecord->TrangThai = 'ThatBai';
                $paymentRecord->MaGiaoDichVNPAY = $transactionNo;
                $paymentRecord->save();
                $message = 'Thanh toan phu phi that bai, don chua duoc cap nhat';

                $booking = DonDat::find($paymentRecord->ID_DD);
                $meta = json_decode($paymentRecord->GhiChu ?? '', true);
                if ($booking && is_array($meta) && ($meta['type'] ?? null) === 'reschedule_surcharge') {
                    $booking->NgayLam = $meta['old_date'] ?? $booking->NgayLam;
                    $booking->GioBatDau = $meta['old_time'] ?? $booking->GioBatDau;
                    $booking->RescheduleCount = max(0, ($booking->RescheduleCount ?? 1) - 1);
                    $booking->FindingStaffResponse = null;
                    $booking->save();

                    ChiTietPhuThu::where('ID_DD', $booking->ID_DD)
                        ->whereIn('ID_PT', ['PT001', 'PT003'])
                        ->delete();
                }

                return redirect()->route('bookings.detail', $paymentRecord->ID_DD)
                    ->with('error', $message);
            } else {
                $walletTx = app(StaffWalletService::class)
                    ->finalizeTopup($txnRef, false, $transactionNo, $responseCode);
                if ($walletTx) {
                    $handledWallet = true;
                    if ($message === 'Thanh toan that bai.') {
                        $message = 'Nap tien that bai.';
                    }
                }
            }
        }

        Log::info('VNPay return handled', [
            'txn_ref'        => $txnRef,
            'response_code'  => $responseCode,
            'transaction_no' => $transactionNo,
            'status'         => $status,
            'valid_signature'=> $isValidSignature,
            'wallet_handled' => $handledWallet,
        ]);

        $appRedirect = $request->query('app_redirect');
        if ($appRedirect) {
            $query = array_filter([
                'status'        => $status,
                'orderId'       => $txnRef,
                'transactionNo' => $transactionNo,
                'responseCode'  => $responseCode,
            ], static fn ($value) => $value !== null && $value !== '');

            $redirectUrl = $appRedirect;
            if (!empty($query)) {
                $redirectUrl .= (str_contains($redirectUrl, '?') ? '&' : '?') . http_build_query($query);
            }

            $safeUrl = htmlspecialchars($redirectUrl, ENT_QUOTES, 'UTF-8');
            $jsUrl = json_encode($redirectUrl, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP);

            $html = <<<HTML
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Dang chuyen ve ung dung</title>
    <meta http-equiv="refresh" content="0;url={$safeUrl}">
    <script>
        setTimeout(function () { window.location.href = {$jsUrl}; }, 100);
    </script>
</head>
<body>
    <p>Dang chuyen ve ung dung. Neu khong tu dong, vui long bam <a href="{$safeUrl}">tai day</a>.</p>
</body>
</html>
HTML;

            return response($html);
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

            $account = TaiKhoan::where('ID_TK', $nhanVien->ID_TK)->first();
            if (!$account) {
                return;
            }

            $service = DichVu::find($booking->ID_DV);
            $title = 'Don moi duoc gan';
            $body = 'Don ' . $booking->ID_DD . ' - Dich vu ' . ($service?->TenDV ?? '');
            $this->sendOneSignalToUser($account, $title, $body, $booking);
        } catch (\Exception $e) {
            // Không làm gián đoạn flow đặt đơn nếu gửi thông báo lỗi
        }
    }

    private function sendOneSignalToUser(TaiKhoan $account, string $title, string $body, DonDat $booking): void
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
            'user_onesignal_player_id' => $account->onesignal_player_id,
        ]);

        $payload = [
            'app_id' => $appId,
            'include_external_user_ids' => [(string)$account->ID_TK],
            'channel_for_external_user_ids' => 'push',
            'include_player_ids' => array_filter([$account->onesignal_player_id]),
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
                'user_id' => $account->ID_TK,
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
            ->whereNotIn('TrangThaiDon', ['cancelled', 'rejected']) // Exclude cancelled and rejected bookings
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
    // =========================================================
    // PHẦN CODE BẠN THÊM VÀO (QUẢN LÝ LỊCH SỬ ĐƠN HÀNG)
    // =========================================================

    public function history()
    {
        if (!Auth::check()) {
            return redirect()->route('login'); 
        }

        $customer = Auth::user()->khachHang;
        if (!$customer) {
            return redirect('/')->with('error', 'Tài khoản chưa cập nhật thông tin khách hàng.');
        }

        $activeStatuses = ['finding_staff', 'assigned', 'confirmed', 'rejected', 'completed', 'working'];
        $historyStatuses = ['completed', 'cancelled', 'failed'];

        $hourCurrent = DonDat::with(['nhanVien', 'lichSuThanhToan', 'dichVu'])
                            ->where('ID_KH', $customer->ID_KH)
                            ->where('LoaiDon', 'hour')
                            ->whereIn('TrangThaiDon', $activeStatuses)
                            ->orderBy('NgayTao', 'desc')
                            ->get();

        $hourHistory = DonDat::with(['nhanVien', 'lichSuThanhToan', 'dichVu'])
                            ->where('ID_KH', $customer->ID_KH)
                            ->where('LoaiDon', 'hour')
                            ->whereIn('TrangThaiDon', $historyStatuses)
                            ->orderBy('NgayTao', 'desc')
                            ->get();

        $monthCurrent = DonDat::with(['nhanVien', 'lichSuThanhToan', 'lichBuoiThang.nhanVien', 'dichVu'])
                            ->where('ID_KH', $customer->ID_KH)
                            ->where('LoaiDon', 'month')
                            ->whereIn('TrangThaiDon', $activeStatuses)
                            ->orderBy('NgayTao', 'desc')
                            ->get();

        $monthHistory = DonDat::with(['nhanVien', 'lichSuThanhToan', 'lichBuoiThang.nhanVien', 'dichVu'])
                            ->where('ID_KH', $customer->ID_KH)
                            ->where('LoaiDon', 'month')
                            ->whereIn('TrangThaiDon', $historyStatuses)
                            ->orderBy('NgayTao', 'desc')
                            ->get();

        return view('account.history', compact('hourCurrent', 'hourHistory', 'monthCurrent', 'monthHistory'));
    }

    public function detail($id)
    {
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        // Tìm đơn hàng theo ID (ID_DD)
        $booking = DonDat::with(['dichVu', 'diaChi', 'chiTietKhuyenMai.khuyenMai', 'nhanVien', 'lichSuThanhToan'])->where('ID_DD', $id)->first();

        // Kiểm tra bảo mật: Đơn này có phải của người đang đăng nhập không?
        $customer = Auth::user()->khachHang;
        
        if (!$booking || $booking->ID_KH !== $customer->ID_KH) {
            return redirect()->route('bookings.history')->with('error', 'Không tìm thấy đơn hàng.');
        }
        
        // Nếu là đơn gói tháng, lấy danh sách các buổi
        $sessions = [];
        if ($booking->LoaiDon === 'month') {
            $sessions = \App\Models\LichBuoiThang::where('ID_DD', $id)
                ->orderBy('NgayLam', 'asc')
                ->get();
        }
        
        $existingRating = \App\Models\DanhGiaNhanVien::where('ID_DD', $id)->first();
        $suggestedTime = $this->suggestNearestAvailableTime($booking);
        
        // Get staff suggestions if in finding_staff state and prompt has been sent
        $staffSuggestions = [];
        if ($booking->TrangThaiDon === 'finding_staff' && $booking->FindingStaffPromptSentAt) {
            $staffSuggestions = $this->getSuggestedStaffAndTime($booking);
        }
        
        return view('account.detail', compact('booking', 'sessions', 'existingRating', 'suggestedTime', 'staffSuggestions'));
    }

    /**
     * Suggest nearest available start time (07-17) that has at least one ready staff.
     */
    private function suggestNearestAvailableTime(DonDat $booking): ?string
    {
        if ($booking->LoaiDon !== 'hour' || !$booking->NgayLam || !$booking->GioBatDau) {
            return null;
        }

        $base = null;
        try {
            $base = Carbon::parse($booking->GioBatDau);
        } catch (\Exception) {
            return null;
        }

        $duration = $booking->ThoiLuongGio
            ?? ($booking->dichVu->ThoiLuong ?? 2);

        $hours = range(7, 17);
        $candidates = [];
        foreach ($hours as $h) {
            $diff = abs($base->hour - $h);
            $candidates[] = ['hour' => $h, 'diff' => $diff];
        }

        usort($candidates, function ($a, $b) {
            if ($a['diff'] === $b['diff']) {
                return $a['hour'] <=> $b['hour'];
            }
            return $a['diff'] <=> $b['diff'];
        });

        foreach ($candidates as $cand) {
            $start = Carbon::parse($booking->NgayLam . ' ' . sprintf('%02d:00:00', $cand['hour']));
            $end = $start->copy()->addHours((float) $duration);

            $hasStaff = LichLamViec::where('NgayLam', $booking->NgayLam)
                ->where('TrangThai', 'ready')
                ->where('GioBatDau', '<=', $start->format('H:i:s'))
                ->where('GioKetThuc', '>=', $end->format('H:i:s'))
                ->exists();

            if ($hasStaff) {
                return $start->format('H:i');
            }
        }

        return null;
    }

    /**
     * Customer submits rating for a completed booking
     */
    public function submitRating(Request $request, string $id)
    {
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        $customer = Auth::user()->khachHang;
        if (!$customer) {
            return back()->with('error', 'Không tìm thấy thông tin khách hàng.');
        }

        $booking = DonDat::where('ID_DD', $id)->first();
        if (!$booking || $booking->ID_KH !== $customer->ID_KH) {
            return redirect()->route('bookings.history')->with('error', 'Không tìm thấy đơn hàng.');
        }

        if (!in_array($booking->TrangThaiDon, ['completed'], true)) {
            return back()->with('error', 'Bạn chỉ có thể đánh giá khi đơn đã hoàn thành.');
        }

        $alreadyRated = DanhGiaNhanVien::where('ID_DD', $booking->ID_DD)->exists();
        if ($alreadyRated) {
            return back()->with('error', 'Bạn đã đánh giá đơn này rồi.');
        }

        $validated = $request->validate([
            'rating' => ['required', 'integer', 'min:1', 'max:5'],
            'comment' => ['nullable', 'string', 'max:2000'],
        ]);

        DanhGiaNhanVien::create([
            'ID_DG' => IdGenerator::next('DanhGiaNhanVien', 'ID_DG', 'DG_'),
            'ID_DD' => $booking->ID_DD,
            'ID_NV' => $booking->ID_NV,
            'ID_KH' => $customer->ID_KH,
            'Diem' => $validated['rating'],
            'NhanXet' => $validated['comment'] ?? null,
            'ThoiGian' => now(),
        ]);

        // Move booking to done so nó nằm trong lịch sử
        if ($booking->TrangThaiDon === 'completed') {
            $oldStatus = $booking->TrangThaiDon;
            $booking->TrangThaiDon = 'completed';
            $booking->save();

            try {
                $notificationService = app(\App\Services\NotificationService::class);
                $notificationService->notifyOrderStatusChanged($booking, $oldStatus, 'completed');
            } catch (\Exception $e) {
                Log::error('Failed to send status change notification after rating', [
                    'booking_id' => $booking->ID_DD,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        return redirect()->route('bookings.detail', $booking->ID_DD)
            ->with('success', 'Cảm ơn bạn đã đánh giá. Đánh giá của bạn đã được lưu.');
    }

    /**
     * Handle customer choice when the system cannot find staff in time.
     */
    public function handleFindingStaffAction(
        Request $request,
        string $id,
        VNPayService $vnPay,
        NotificationService $notificationService
    ) {
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        $customer = Auth::user()->khachHang;
        if (!$customer) {
            return back()->with('error', 'Khong tim thay thong tin khach hang.');
        }

        $booking = DonDat::with('lichSuThanhToan')
            ->where('ID_DD', $id)
            ->where('ID_KH', $customer->ID_KH)
            ->first();

        if (!$booking) {
            return back()->with('error', 'Khong tim thay don dat.');
        }

        if ($booking->TrangThaiDon !== 'finding_staff' || $booking->LoaiDon !== 'hour') {
            return back()->with('error', 'Chi ho tro don theo gio dang tim nhan vien.');
        }

        // Check reschedule limit (max 1 time)
        if (($booking->RescheduleCount ?? 0) >= 1) {
            return back()->with('error', 'Bạn chỉ được thay đổi thời gian đơn hàng 1 lần duy nhất.');
        }

        $validated = $request->validate([
            'action' => ['required', 'in:wait,reschedule'],
            'new_date' => ['required_if:action,reschedule', 'date'],
            'new_time' => ['required_if:action,reschedule', 'date_format:H:i'],
        ]);

        if ($validated['action'] === 'wait') {
            $booking->FindingStaffResponse = 'wait';
            if (!$booking->FindingStaffPromptSentAt) {
                $booking->FindingStaffPromptSentAt = now();
            }
            $booking->save();

            return redirect()->route('bookings.detail', $booking->ID_DD)
                ->with('success', 'Da ghi nhan ban tiep tuc cho nhan vien.');
        }

        $newDate = $validated['new_date'];
        $newTime = $validated['new_time'];
        $newStart = Carbon::parse($newDate . ' ' . $newTime);

        if ($newStart->hour < 7 || $newStart->hour > 17) {
            return back()->with('error', 'Chi cho phep doi trong khung 07:00 - 17:00.')->withInput();
        }

        if ($newStart->lessThanOrEqualTo(Carbon::now())) {
            return back()->with('error', 'Thoi gian moi phai lon hon hien tai.')->withInput();
        }

        $limitDate = Carbon::now()->addDays(7)->endOfDay();
        if ($newStart->greaterThan($limitDate)) {
            return back()->with('error', 'Chi cho phep doi trong 7 ngay ke tu hom nay.')->withInput();
        }

        $oldHour = $booking->GioBatDau ? Carbon::parse($booking->GioBatDau)->hour : null;
        $newHour = $newStart->hour;

        $oldDate = $booking->NgayLam;
        $oldTime = $booking->GioBatDau;

        $booking->NgayLam = $newDate;
        $booking->GioBatDau = $newStart->format('H:i:s');
        $booking->FindingStaffResponse = 'reschedule';
        $booking->RescheduleCount = ($booking->RescheduleCount ?? 0) + 1;

        $surchargeAmount = 30000;
        $weekendSurchargeAmount = optional(\App\Models\PhuThu::find('PT003'))->GiaCuoc ?? 30000;
        $additionalSurcharges = [];

        $hasPt001 = \App\Models\ChiTietPhuThu::where('ID_DD', $booking->ID_DD)
            ->where('ID_PT', 'PT001')
            ->exists();
        $hasPt003 = \App\Models\ChiTietPhuThu::where('ID_DD', $booking->ID_DD)
            ->where('ID_PT', 'PT003')
            ->exists();

        $needsSurcharge = ($newHour < 8 || $newHour == 17)
        && ($oldHour === null || !($oldHour < 8 || $oldHour == 17))
        && !$hasPt001;

        $isWeekend = in_array($newStart->dayOfWeek, [0, 6], true);
        $needsWeekendSurcharge = $isWeekend && !$hasPt003;

        $totalSurcharge = 0;
        if ($needsSurcharge) {
            $additionalSurcharges[] = ['id' => 'PT001', 'amount' => $surchargeAmount, 'note' => 'Phu thu doi gio 7h/17h'];
            $totalSurcharge += $surchargeAmount;
        }
        if ($needsWeekendSurcharge) {
            $additionalSurcharges[] = ['id' => 'PT003', 'amount' => $weekendSurchargeAmount, 'note' => 'Phu thu cuoi tuan'];
            $totalSurcharge += $weekendSurchargeAmount;
        }

        $paymentUrl = null;
        $rescheduleMeta = [
            'type'      => 'reschedule_surcharge',
            'old_date'  => $oldDate,
            'old_time'  => $oldTime,
            'new_date'  => $newDate,
            'new_time'  => $newStart->format('H:i:s'),
        ];

        if ($totalSurcharge > 0) {
            foreach ($additionalSurcharges as $item) {
                \App\Models\ChiTietPhuThu::create([
                    'ID_PT' => $item['id'],
                    'ID_DD' => $booking->ID_DD,
                    'Ghichu' => $item['note'],
                ]);
            }

            $booking->TongTien = ($booking->TongTien ?? 0) + $totalSurcharge;
            $booking->TongTienSauGiam = ($booking->TongTienSauGiam ?? $booking->TongTien ?? 0) + $totalSurcharge;

            $payment = $booking->lichSuThanhToan->first();
            $paymentMethod = $payment?->PhuongThucThanhToan ?? 'TienMat';

            if ($paymentMethod === 'VNPay') {
                $paymentId = IdGenerator::next('LichSuThanhToan', 'ID_LSTT', 'LSTT_');

                LichSuThanhToan::create([
                    'ID_LSTT' => $paymentId,
                    'PhuongThucThanhToan' => 'VNPay',
                    'TrangThai' => 'ChoXuLy',
                    'SoTienThanhToan' => $totalSurcharge,
                    'ID_DD' => $booking->ID_DD,
                    'LoaiGiaoDich' => 'payment',
                    'GhiChu' => json_encode($rescheduleMeta),
                ]);

                $paymentUrl = $vnPay->createPaymentUrl([
                    'txn_ref' => $paymentId,
                    'amount' => $totalSurcharge,
                    'order_info' => 'Phu thu doi gio don ' . $booking->ID_DD,
                ]);
            } else {
                LichSuThanhToan::create([
                    'ID_LSTT' => IdGenerator::next('LichSuThanhToan', 'ID_LSTT', 'LSTT_'),
                    'PhuongThucThanhToan' => 'TienMat',
                    'TrangThai' => 'ChoXuLy',
                    'SoTienThanhToan' => $totalSurcharge,
                    'ID_DD' => $booking->ID_DD,
                    'LoaiGiaoDich' => 'payment',
                    'GhiChu' => json_encode($rescheduleMeta),
                ]);
            }
        }
        $booking->save();

        if ($paymentUrl) {
            return redirect()->away($paymentUrl);
        }

        try {
            $notificationService->notifyOrderRescheduled($booking);
        } catch (\Exception $e) {
            Log::error('Failed to send reschedule notification', [
                'booking_id' => $booking->ID_DD,
                'error' => $e->getMessage(),
            ]);
        }

        return redirect()->route('bookings.detail', $booking->ID_DD)
            ->with('success', 'Da cap nhat thoi gian bat dau don.');
    }

    /**
     * Cancel a booking with refund logic
     */
    public function cancelBooking($id)
    {
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        $customer = Auth::user()->khachHang;
        if (!$customer) {
            return back()->with('error', 'Không tìm thấy thông tin khách hàng.');
        }

        $booking = DonDat::with(['dichVu', 'lichBuoiThang'])->where('ID_DD', $id)->first();

        // Check ownership
        if (!$booking || $booking->ID_KH !== $customer->ID_KH) {
            return back()->with('error', 'Không tìm thấy đơn hàng.');
        }

        // Check if already cancelled or done
        if (in_array($booking->TrangThaiDon, ['cancelled', 'completed'])) {
            return back()->with('error', 'Không thể hủy đơn hàng này.');
        }

        // Check 12-hour rule
        $startTime = null;
        if ($booking->LoaiDon === 'hour') {
            if ($booking->NgayLam && $booking->GioBatDau) {
                $startTime = \Carbon\Carbon::parse($booking->NgayLam . ' ' . $booking->GioBatDau);
            }
        } else { // month
            $startTime = $booking->NgayBatDauGoi ? \Carbon\Carbon::parse($booking->NgayBatDauGoi) : null;
        }

        if ($startTime && now()->diffInHours($startTime, false) < 12) {
            return back()->with('error', 'Không thể hủy đơn trong vòng 12 giờ trước giờ bắt đầu.');
        }

        try {
            DB::beginTransaction();

            // Use RefundService to handle refund logic
            $refundService = app(RefundService::class);
            $notificationService = app(NotificationService::class);
            
            $refundResult = $refundService->refundOrder($booking, 'user_cancel');

            if (!$refundResult['success']) {
                DB::rollBack();
                return back()->with('error', $refundResult['message']);
            }

            // Update booking status
            $booking->TrangThaiDon = 'cancelled';
            $booking->save();

            // For monthly orders, cancel only incomplete sessions
            if ($booking->LoaiDon === 'month') {
                \App\Models\LichBuoiThang::where('ID_DD', $booking->ID_DD)
                    ->where('TrangThaiBuoi', '!=', 'completed')
                    ->update(['TrangThaiBuoi' => 'cancelled']);
            }

            // Send notification to customer
            $notificationService->notifyOrderCancelled($booking, 'user_cancel', $refundResult);

            DB::commit();

            // Create appropriate success message
            $message = 'Đã hủy đơn hàng thành công.';
            if ($refundResult['amount'] > 0) {
                $message .= ' Số tiền hoàn: ' . number_format($refundResult['amount']) . ' đ';
            } elseif ($refundResult['payment_method'] === 'TienMat') {
                $message .= ' (Đơn thanh toán bằng tiền mặt)';
            }

            return redirect()->route('bookings.history')->with('success', $message);

        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Booking cancellation failed', [
                'booking_id' => $id,
                'error' => $e->getMessage(),
            ]);
            return back()->with('error', 'Lỗi: ' . $e->getMessage());
        }
    }

    /**
     * Call VNPay refund API
     */
    private function callVnpayRefund($booking, $payment, $refundAmount = null)
    {
        $requestId = (string) \Str::uuid();
        $createDate = now()->format('YmdHis');
        
        // Use provided refund amount or full booking amount
        $actualRefundAmount = $refundAmount ?? $booking->TongTienSauGiam;
        $amount = (int) round($actualRefundAmount * 100);
        
        // Log for debugging
        \Log::info('VNPay Refund Request', [
            'booking_id' => $booking->ID_DD,
            'original_amount' => $booking->TongTienSauGiam,
            'refund_amount' => $actualRefundAmount,
            'vnp_amount' => $amount,
            'payment_transaction_no' => $payment->MaGiaoDichVNPAY,
        ]);
        
        // Validate refund amount
        if ($amount <= 0) {
            return [
                'success' => false,
                'message' => 'Số tiền hoàn phải lớn hơn 0',
            ];
        }
        
        if ($actualRefundAmount > $booking->TongTienSauGiam) {
            return [
                'success' => false,
                'message' => 'Số tiền hoàn không được lớn hơn số tiền đã thanh toán',
            ];
        }


        // Determine transaction type: 02 for full refund, 03 for partial refund
        $transactionType = ($actualRefundAmount >= $booking->TongTienSauGiam) ? '02' : '03';

        $payload = [
            'vnp_RequestId' => $requestId,
            'vnp_Version' => config('vnpay.version'),
            'vnp_Command' => 'refund',
            'vnp_TmnCode' => config('vnpay.tmn_code'),
            'vnp_TransactionType' => $transactionType,
            'vnp_TxnRef' => $booking->ID_DD,
            'vnp_Amount' => $amount,
            'vnp_TransactionNo' => $payment->MaGiaoDichVNPAY,
            'vnp_TransactionDate' => $payment->ThoiGian ? \Carbon\Carbon::parse($payment->ThoiGian)->format('YmdHis') : $createDate,
            'vnp_CreateBy' => Auth::user()->email ?? 'system',
            'vnp_CreateDate' => $createDate,
            'vnp_IpAddr' => request()->ip() ?? '127.0.0.1',
            'vnp_OrderInfo' => 'Hoan tien don hang ' . $booking->ID_DD,
        ];

        $data = implode('|', [
            $payload['vnp_RequestId'],
            $payload['vnp_Version'],
            $payload['vnp_Command'],
            $payload['vnp_TmnCode'],
            $payload['vnp_TransactionType'],
            $payload['vnp_TxnRef'],
            $payload['vnp_Amount'],
            $payload['vnp_TransactionNo'],
            $payload['vnp_TransactionDate'],
            $payload['vnp_CreateBy'],
            $payload['vnp_CreateDate'],
            $payload['vnp_IpAddr'],
            $payload['vnp_OrderInfo'],
        ]);

        $secretKey = config('vnpay.hash_secret');
        $payload['vnp_SecureHash'] = hash_hmac('sha512', $data, $secretKey);

        $response = \Http::withHeaders([
            'Content-Type' => 'application/json',
        ])->post(config('vnpay.refund_url'), $payload);

        $body = $response->json();

        if (($body['vnp_ResponseCode'] ?? null) === '00') {
            return ['success' => true];
        }

        return [
            'success' => false,
            'message' => $body['vnp_Message'] ?? 'Lỗi không xác định',
        ];
    }
    /**
     * Get top 3 suggested staff members with their nearest available time slots
     */
    private function getSuggestedStaffAndTime(DonDat $booking): array
    {
        if ($booking->LoaiDon !== 'hour' || !$booking->NgayLam || !$booking->GioBatDau) {
            return [];
        }

        $duration = $booking->ThoiLuongGio ?? ($booking->dichVu->ThoiLuong ?? 2);
        $originalDate = Carbon::parse($booking->NgayLam);
        $originalStart = Carbon::parse($booking->GioBatDau);
        
        // Get customer address for proximity calculation
        $customerQuan = null;
        if ($booking->diaChi) {
            $diaChiText = $booking->diaChi->DiaChiDayDu ?? '';
            if ($diaChiText !== '') {
                $customerQuan = $this->guessQuanFromAddress($diaChiText);
            }
        }

        $suggestions = [];
        
        // Search in original date and ±3 days nearby
        $datesToSearch = [];
        for ($i = 0; $i <= 3; $i++) {
            if ($i == 0) {
                $datesToSearch[] = $originalDate->copy();
            } else {
                $datesToSearch[] = $originalDate->copy()->addDays($i);
                $datesToSearch[] = $originalDate->copy()->subDays($i);
            }
        }

        foreach ($datesToSearch as $searchDate) {
            // Skip past dates
            if ($searchDate->isPast()) {
                continue;
            }
            
            $ngayLam = $searchDate->format('Y-m-d');
            
            // Find all staff schedules for this date
            $lichLamViec = LichLamViec::with('nhanVien')
                ->where('NgayLam', $ngayLam)
                ->where('TrangThai', 'ready')
                ->get();

            foreach ($lichLamViec as $lich) {
                $nv = $lich->nhanVien;
                if (!$nv) {
                    continue;
                }

                // Calculate rating score
                $avgScore = DanhGiaNhanVien::where('ID_NV', $nv->ID_NV)->avg('Diem');
                $ratingPercent = $avgScore ? round(((float) $avgScore) / 5 * 100) : 30;

                // Calculate proximity score
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

                // Find nearest available time slot for this staff
                $scheduleStart = Carbon::parse($lich->GioBatDau);
                $scheduleEnd = Carbon::parse($lich->GioKetThuc);
                
                // Find the nearest time slot that fits within the schedule
                $bestTime = null;
                $minDiff = PHP_INT_MAX;
                
                // Try different start times within the schedule
                $currentTime = $scheduleStart->copy();
                while ($currentTime->copy()->addHours($duration)->lessThanOrEqualTo($scheduleEnd)) {
                    $potentialEnd = $currentTime->copy()->addHours($duration);
                    
                    // Check if this time slot has no conflicts
                    $hasConflict = $this->hasTimeConflict(
                        $nv->ID_NV,
                        $ngayLam,
                        $currentTime->format('H:i:s'),
                        $potentialEnd->format('H:i:s')
                    );
                    
                    if (!$hasConflict) {
                        // Calculate time difference from original booking datetime
                        $proposedDateTime = Carbon::parse($ngayLam . ' ' . $currentTime->format('H:i:s'));
                        $originalDateTime = Carbon::parse($booking->NgayLam . ' ' . $booking->GioBatDau);
                        $diff = abs($proposedDateTime->diffInMinutes($originalDateTime));
                        
                        if ($diff < $minDiff) {
                            $minDiff = $diff;
                            $bestTime = $currentTime->copy();
                        }
                    }
                    
                    // Move to next 30-minute interval
                    $currentTime->addMinutes(30);
                }

                if ($bestTime) {
                    // Handle avatar
                    $hinhAnh = $nv->HinhAnh;
                    if (empty($hinhAnh)) {
                        $hinhAnh = 'https://ui-avatars.com/api/?name=' . urlencode($nv->Ten_NV) . '&background=004d2e&color=fff&size=150';
                    } elseif (!str_starts_with($hinhAnh, 'http')) {
                        $hinhAnh = url('storage/' . ltrim($hinhAnh, '/'));
                    }

                    $jobsCompleted = DonDat::where('ID_NV', $nv->ID_NV)
                        ->where('TrangThaiDon', 'done')
                        ->count();

                    $score = $ratingPercent * 0.3 + $proximityPercent * 0.7;
                    
                    // Penalty for different dates (prefer same date)
                    $daysDiff = abs($searchDate->diffInDays($originalDate));
                    $datePenalty = $daysDiff * 10; // 10 points penalty per day difference
                    $adjustedScore = max(0, $score - $datePenalty);

                    $suggestions[] = [
                        'id_nv' => $nv->ID_NV,
                        'ten_nv' => $nv->Ten_NV,
                        'hinh_anh' => $hinhAnh,
                        'sdt' => $nv->SDT,
                        'rating_percent' => $ratingPercent,
                        'avg_rating' => $avgScore ? round($avgScore, 1) : 0,
                        'proximity_percent' => $proximityPercent,
                        'score' => (float) $adjustedScore,
                        'jobs_completed' => $jobsCompleted,
                        'suggested_date' => $searchDate->format('Y-m-d'),
                        'suggested_time' => $bestTime->format('H:i'),
                        'time_diff_minutes' => $minDiff,
                        'days_diff' => $daysDiff,
                    ];
                }
            }
            
            // If we have enough suggestions, stop searching further dates
            if (count($suggestions) >= 10) {
                break;
            }
        }

        // Sort by score (highest first)
        usort($suggestions, static function (array $a, array $b): int {
            return $b['score'] <=> $a['score'];
        });

        // Return top 3
        return array_slice($suggestions, 0, 3);
    }

    /**
     * Apply a staff suggestion when user clicks on it
     */
    public function applyStaffSuggestion(Request $request, string $id, VNPayService $vnPay)
    {
        if (!Auth::check()) {
            return response()->json(['error' => 'Chưa đăng nhập'], 401);
        }

        $customer = Auth::user()->khachHang;
        if (!$customer) {
            return response()->json(['error' => 'Không tìm thấy thông tin khách hàng'], 403);
        }

        $booking = DonDat::with(['dichVu', 'lichSuThanhToan'])
            ->where('ID_DD', $id)
            ->where('ID_KH', $customer->ID_KH)
            ->first();

        if (!$booking) {
            return response()->json(['error' => 'Không tìm thấy đơn đặt'], 404);
        }

        if ($booking->TrangThaiDon !== 'finding_staff' || $booking->LoaiDon !== 'hour') {
            return response()->json(['error' => 'Chỉ hỗ trợ đơn theo giờ đang tìm nhân viên'], 422);
        }

        // Check reschedule limit (max 1 time)
        if (($booking->RescheduleCount ?? 0) >= 1) {
            return response()->json(['error' => 'Bạn chỉ được thay đổi thời gian đơn hàng 1 lần duy nhất'], 422);
        }

        $validated = $request->validate([
            'id_nv' => ['required', 'string', 'exists:NhanVien,ID_NV'],
            'suggested_date' => ['required', 'date'],
            'suggested_time' => ['required', 'date_format:H:i'],
        ]);

        $newDate = $validated['suggested_date'];
        $newTime = $validated['suggested_time'];
        $newStaffId = $validated['id_nv'];
        
        // Verify the datetime is valid
        $newStart = Carbon::parse($newDate . ' ' . $newTime);
        if ($newStart->hour < 7 || $newStart->hour > 17) {
            return response()->json(['error' => 'Giờ bắt đầu phải trong khung 07:00 - 17:00'], 422);
        }

        if ($newStart->lessThanOrEqualTo(Carbon::now())) {
            return response()->json(['error' => 'Thời gian phải lớn hơn hiện tại'], 422);
        }

        // Check for surcharge requirement
        $oldHour = $booking->GioBatDau ? Carbon::parse($booking->GioBatDau)->hour : null;
        $newHour = $newStart->hour;

        $surchargeAmount = 30000;
        $hasPt001 = \App\Models\ChiTietPhuThu::where('ID_DD', $booking->ID_DD)
            ->where('ID_PT', 'PT001')
            ->exists();

        $needsSurcharge = ($newHour < 8 || $newHour == 17)
            && ($oldHour === null || !($oldHour < 8 || $oldHour == 17))
            && !$hasPt001;

        $paymentUrl = null;

        // Update booking
        $booking->NgayLam = $newDate;
        $booking->GioBatDau = $newStart->format('H:i:s');
        $booking->ID_NV = $newStaffId;
        $booking->TrangThaiDon = 'assigned';
        $booking->FindingStaffResponse = 'reschedule';
        $booking->RescheduleCount = ($booking->RescheduleCount ?? 0) + 1;

        // Handle surcharge if needed
        if ($needsSurcharge) {
            \App\Models\ChiTietPhuThu::create([
                'ID_PT' => 'PT001',
                'ID_DD' => $booking->ID_DD,
                'Ghichu' => 'Phụ thu đổi giờ 7h/17h',
            ]);

            $booking->TongTien = ($booking->TongTien ?? 0) + $surchargeAmount;
            $booking->TongTienSauGiam = ($booking->TongTienSauGiam ?? $booking->TongTien ?? 0) + $surchargeAmount;

            $payment = $booking->lichSuThanhToan->first();
            $paymentMethod = $payment?->PhuongThucThanhToan ?? 'TienMat';

            if ($paymentMethod === 'VNPay') {
                $paymentId = IdGenerator::next('LichSuThanhToan', 'ID_LSTT', 'LSTT_');

                LichSuThanhToan::create([
                    'ID_LSTT' => $paymentId,
                    'PhuongThucThanhToan' => 'VNPay',
                    'TrangThai' => 'ChoXuLy',
                    'SoTienThanhToan' => $surchargeAmount,
                    'ID_DD' => $booking->ID_DD,
                    'LoaiGiaoDich' => 'payment',
                    'GhiChu' => 'Phụ thu đổi giờ cao điểm (trước 8h hoặc 17h)',
                ]);

                $paymentUrl = $vnPay->createPaymentUrl([
                    'txn_ref' => $paymentId,
                    'amount' => $surchargeAmount,
                    'order_info' => 'Phụ thu đổi giờ đơn ' . $booking->ID_DD,
                ]);
            } else {
                // For cash payment, just create a record but don't require VNPay payment
                LichSuThanhToan::create([
                    'ID_LSTT' => IdGenerator::next('LichSuThanhToan', 'ID_LSTT', 'LSTT_'),
                    'PhuongThucThanhToan' => 'TienMat',
                    'TrangThai' => 'ChoXuLy',
                    'SoTienThanhToan' => $surchargeAmount,
                    'ID_DD' => $booking->ID_DD,
                    'LoaiGiaoDich' => 'payment',
                    'GhiChu' => 'Phụ thu đổi giờ cao điểm (trước 8h hoặc 17h)',
                ]);
            }
        }

        $booking->save();

        // Notify staff
        try {
            $this->notifyStaffAssigned($booking);
        } catch (\Exception $e) {
            Log::error('Failed to notify staff after suggestion applied', [
                'booking_id' => $booking->ID_DD,
                'error' => $e->getMessage(),
            ]);
        }

        // Send notification about reschedule
        try {
            $notificationService = app(NotificationService::class);
            $notificationService->notifyOrderRescheduled($booking);
        } catch (\Exception $e) {
            Log::error('Failed to send reschedule notification', [
                'booking_id' => $booking->ID_DD,
                'error' => $e->getMessage(),
            ]);
        }

        // If there's a payment URL, return it for redirect
        if ($paymentUrl) {
            return response()->json([
                'success' => true,
                'requires_payment' => true,
                'payment_url' => $paymentUrl,
                'message' => 'Cần thanh toán phụ thu 30,000đ cho giờ cao điểm',
            ]);
        }

        return response()->json([
            'success' => true,
            'requires_payment' => false,
            'message' => $needsSurcharge 
                ? 'Đã cập nhật đơn đặt và thêm phụ thu 30,000đ (thanh toán tiền mặt)'
                : 'Đã cập nhật đơn đặt với nhân viên và thời gian gợi ý',
            'data' => [
                'new_date' => $newStart->format('d/m/Y'),
                'new_time' => $newStart->format('H:i'),
                'staff_name' => NhanVien::find($newStaffId)->Ten_NV ?? '',
                'surcharge_added' => $needsSurcharge,
                'surcharge_amount' => $needsSurcharge ? $surchargeAmount : 0,
            ],
        ]);
    }

    public function cancelSession(Request $request)
    {
        if (!Auth::check()) {
            return response()->json(['success' => false, 'message' => 'Vui lòng đăng nhập.']);
        }

        $request->validate([
            'session_id' => 'required'
        ]);

        $customer = Auth::user()->khachHang;
        if (!$customer) {
            return response()->json(['success' => false, 'message' => 'Không tìm thấy thông tin khách hàng.']);
        }

        $session = \App\Models\LichBuoiThang::with(['donDat', 'nhanVien'])->findOrFail($request->session_id);

        // Check ownership
        if ($session->donDat->ID_KH !== $customer->ID_KH) {
            return response()->json(['success' => false, 'message' => 'Bạn không có quyền hủy buổi làm này.']);
        }
        
        if ($session->TrangThaiBuoi === 'cancelled') {
            return response()->json(['success' => false, 'message' => 'Buổi làm này đã bị hủy trước đó.']);
        }

        if ($session->TrangThaiBuoi === 'completed') {
            return response()->json(['success' => false, 'message' => 'Không thể hủy buổi làm đã hoàn thành.']);
        }

        // Check 12-hour rule
        $startTime = \Carbon\Carbon::parse($session->NgayLam . ' ' . $session->GioBatDau);
        if (now()->diffInHours($startTime, false) < 12) {
            return response()->json(['success' => false, 'message' => 'Không thể hủy buổi làm trong vòng 12 giờ trước giờ bắt đầu.']);
        }

        // 1. Refund logic
        $refundService = app(RefundService::class);
        $refundResult = $refundService->refundSession($session, 'user_cancel_session');

        if (!$refundResult['success']) {
            return response()->json([
                'success' => false,
                'message' => 'Lỗi hoàn tiền VNPay: ' . ($refundResult['message'] ?? 'Không xác định')
            ]);
        }
        
        // 2. Update Session Status
        $session->TrangThaiBuoi = 'cancelled';
        $session->save();

        // 3. Notify Staff & Update Schedule
        if ($session->ID_NV) {
            $staff = $session->nhanVien;
            
            // Update LichLamViec status back to 'ready'
            \App\Models\LichLamViec::where('ID_NV', $session->ID_NV)
                ->where('NgayLam', $session->NgayLam)
                ->where('GioBatDau', '<=', $session->GioBatDau)
                ->where('TrangThai', 'assigned')
                ->update(['TrangThai' => 'ready']);

            // Send Email to Staff
            try {
                \Mail::to($staff->Email)->send(new \App\Mail\StaffSessionCancelledMail([
                    'staff_name' => $staff->Ten_NV,
                    'session_date' => \Carbon\Carbon::parse($session->NgayLam)->format('d/m/Y'),
                    'session_time' => \Carbon\Carbon::parse($session->GioBatDau)->format('H:i'),
                    'order_id' => $session->ID_DD,
                    'reason' => 'Khách hàng yêu cầu hủy',
                ]));
            } catch (\Exception $e) {
                \Log::error('Failed to send staff cancellation email: ' . $e->getMessage());
            }
        }

        // 4. Notify Customer
        $notificationService = app(NotificationService::class);
        $notificationService->notifySessionCancelled($session, 'user_cancel_session', [
            'amount' => $refundResult['amount'],
            'payment_method' => $refundResult['payment_method']
        ]);

        // 5. Check if all sessions are cancelled -> Cancel Order
        $booking = $session->donDat;
        $allSessions = \App\Models\LichBuoiThang::where('ID_DD', $booking->ID_DD)->get();
        $cancelledCount = $allSessions->where('TrangThaiBuoi', 'cancelled')->count();
        
        if ($cancelledCount === $allSessions->count()) {
            $booking->TrangThaiDon = 'cancelled';
            $booking->save();

            // Notify order cancelled
            $notificationService->notifyOrderCancelled($booking, 'user_cancel', [
                'amount' => 0, // Already refunded per session
                'payment_method' => $refundResult['payment_method']
            ]);
        }

        // Build appropriate message based on refund status
        $message = 'Hủy buổi làm thành công.';
        if ($refundResult['pending_refund'] ?? false) {
            $message .= '';
        }

        return response()->json(['success' => true, 'message' => $message]);
    }

    }
