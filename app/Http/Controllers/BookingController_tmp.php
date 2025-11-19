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
                    if ($nvQuan && $nvQuan->ViDo !== null && $customerQuan->ViDo !== null) {
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
            return response()->json(['error' => 'Vui lA2ng �`a?ng nhA?p trA��c khi A�p mA� khuyA�n mA�i'], 403);
        }

        $km = KhuyenMai::where('ID_KM', $code)
            ->where('TrangThai', 'activated')
            ->first();

        if (!$km) {
            return response()->json(['error' => 'Mã khuyến mãi không hợp lệ hoặc đã ngưng kích hoạt'], 422);
        }

        $today = Carbon::today();
        if (($km->NgayBatDau && $today->lt(Carbon::parse($km->NgayBatDau))) ||
            ($km->NgayKetThuc && $today->gt(Carbon::parse($km->NgayKetThuc)))) {
            return response()->json(['error' => 'Mã khuyến mãi đã hết hạn'], 422);
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
            'loai_don'      => ['required', 'in:hour,month'],
            'id_dv'         => ['required', 'string'],
            'id_dc'         => ['nullable', 'string'],
            'dia_chi_text'  => ['nullable', 'string'],
            'dia_chi_unit'  => ['nullable', 'string'],
            'dia_chi_street'=> ['nullable', 'string'],
            'ngay_lam'      => ['nullable', 'date'],
            'gio_bat_dau'   => ['nullable', 'date_format:H:i'],
            'thoi_luong'    => ['nullable', 'integer', 'min:1'],
            'tong_tien'     => ['required', 'numeric', 'min:0'],
            'tong_sau_giam' => ['nullable', 'numeric', 'min:0'],
            'id_nv'         => ['nullable', 'string'],
            'id_km'         => ['nullable', 'string'],
            'vouchers'                  => ['nullable', 'array'],
            'vouchers.*.id_km'          => ['required_with:vouchers', 'string'],
            'vouchers.*.tien_giam'      => ['required_with:vouchers', 'numeric', 'min:0'],
            'ghi_chu'       => ['nullable', 'string'],
        ]);

        $paymentMethod = $validated['payment_method'] ?? 'vnpay';

        $account = Auth::user();
        $customer = $account?->khachHang;

        if (!$customer) {
            return response()->json(['error' => 'Không tìm thấy khách hàng'], 403);
        }

        $prefix = $validated['loai_don'] === 'month'
            ? 'DD_month_'
            : 'DD_hour_';
        $idDon = IdGenerator::next('DonDat', 'ID_DD', $prefix);

        $appliedVouchers = $validated['vouchers'] ?? [];

        // KhA'ng cho khA-ch hA�ng A�p lA�i bA?t kA� mA� khuyA�n mA�i nA�o A�A� tA?ng s��` d��ng trA??c A�A�y
        if (!empty($appliedVouchers)) {
            foreach ($appliedVouchers as $voucher) {
                if (empty($voucher['id_km'])) {
                    continue;
                }

                $code = $voucher['id_km'];

                $hasUsed = DonDat::where('ID_KH', $customer->ID_KH)
                    ->where(function ($query) use ($code) {
                        $query->where('ID_KM', $code)
                            ->orWhereExists(function ($sub) use ($code) {
                                $sub->selectRaw('1')
                                    ->from('ChiTietKhuyenMai')
                                    ->whereColumn('ChiTietKhuyenMai.ID_DD', 'DonDat.ID_DD')
                                    ->where('ChiTietKhuyenMai.ID_KM', $code);
                            });
                    })
                    ->exists();

                if ($hasUsed) {
                    return response()->json([
                        'error' => 'M�� khuyA�n mA�i ' . $code . ' bA?n A� t��`ng s��` d��ng cho �`����n trA??c A�A�y nA�n khA'ng th��� A�p lA�i.',
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

        // Xử lý địa chỉ: tìm ID_DC phù hợp hoặc tạo mới
        $idDc = $validated['id_dc'] ?? null;
        $diaChiText = isset($validated['dia_chi_text'])
            ? trim((string) $validated['dia_chi_text'])
            : '';

        if ($diaChiText !== '') {
            // �`��c lA� pA-n hiA�n �`ang lA� chu��>i kA?t hA?p tA� t��� select-address:
            //  - N���u khA'ch nh��?p unit-address:  "unit, <streetAddress>"
            //  - N���u khA'ng nh��?p unit-address:  "<streetAddress>"
            // TA?i confirm, ta uu tiA�n dA?ng thA?ng hai trA??ng rieng neu co.
            $rawUnit   = $validated['dia_chi_unit']   ?? null;
            $rawStreet = $validated['dia_chi_street'] ?? null;

            $canHo = $rawUnit !== null && trim($rawUnit) !== ''
                ? trim($rawUnit)
                : null;

            $full = $rawStreet !== null && trim($rawStreet) !== ''
                ? trim($rawStreet)
                : $diaChiText;

            // KhA'ng tA?o mA?i / cA?p nhA?t �`��<a ch��% �`A? luu cua khA'ch
            // o day. Chi thu xem co A`�a chi nao da ton tai trong danh sach
            // dia chi da luu cua khA'ch thi tai su dung ID_DC do; neu khong
            // thi tao dia chi moi nhung khong gan ID_KH (�`a?A�a chi chi gan
            // voi �`A?n A`a�t, khA'ng toi la dia chi da luu cua khA'ch).
            $query = $customer->diaChis()->where('DiaChiDayDu', $full);
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
                    'ID_DC'        => $newIdDc,
                    'ID_KH'        => null,
                    'ID_Quan'      => $quan?->ID_Quan,
                    'CanHo'        => $canHo,
                    'DiaChiDayDu'  => $full,
                ]);

                $idDc = $newIdDc;
            }
        }

        $trangThaiDon = 'unpaid';
        if ($paymentMethod === 'cash') {
            $trangThaiDon = 'unpaid';
        } elseif ($paymentMethod === 'vnpay') {
            if ($selectedStaffId) {
                $trangThaiDon = 'wait_confirm';
            } else {
                $trangThaiDon = 'finding_staff';
            }
        }

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
            // Giữ nguyên trạng thái cũ để khớp với schema DB
            'TrangThaiDon'   => $trangThaiDon,
            'TongTien'       => $tongTien,
            'TongTienSauGiam'=> $tongSauGiam,
            'ID_NV'          => $validated['id_nv'],
            'ID_KM'          => $validated['id_km'] ?? null,
        ]);

        // Lưu chi tiết khuyến mãi (hỗ trợ nhiều KM cho 1 đơn)
        if (!empty($appliedVouchers)) {
            foreach ($appliedVouchers as $voucher) {
                if (empty($voucher['id_km'])) {
                    continue;
                }

                ChiTietKhuyenMai::create([
                    'ID_DD'    => $idDon,
                    'ID_KM'    => $voucher['id_km'],
                    'TienGiam' => isset($voucher['tien_giam'])
                        ? (float) $voucher['tien_giam']
                        : 0.0,
                ]);
            }
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

        DonDat::where('ID_DD', $idDon)->update([
            'TrangThaiDon' => $trangThaiDon,
        ]);

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
    $message = 'Thanh toán thất bại.';

    if ($isValidSignature && $responseCode === '00') {
        $status  = 'success';
        $message = 'Thanh toán thành công.';

        if ($txnRef) {
            $order = DonDat::find($txnRef);
            if ($order) {
                // giữ logic assigned / finding_staff như bạn muốn
                if ($order->ID_NV) {
                    $order->TrangThaiDon = 'assigned';
                } else {
                    $order->TrangThaiDon = 'finding_staff';
                }
                $order->save();

                // Cập nhật lịch sử thanh toán VNPay
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
        $message = 'Chữ ký không hợp lệ.';
    }

    return view('payment-result', [
        'status'        => $status,
        'message'       => $message,
        'orderId'       => $txnRef,
        'transactionNo' => $transactionNo,
        'responseCode'  => $responseCode,
    ]);
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
            // VD: 140 Le Trong Tan, Tay Thanh, Tan Phu, Thanh pho Ho Chi Minh
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

        // Tim quan/huyen bang like truoc
        $quan = Quan::where('TenQuan', 'like', '%' . $candidate . '%')->first();
        if ($quan) {
            return $quan;
        }

        // Thu loai bo cac tien to thong dung trong TenQuan va A`�a chi
        $normalize = static function (string $value): string {
            $value = preg_replace('/^(Quận|Huyện|TP\\.?|Thành phố)\\s+/iu', '', $value);
            return trim((string) $value);
        };

        $normalizedCandidate = $normalize($candidate);
        if ($normalizedCandidate === '') {
            return null;
        }

        $quans = Quan::all();

        foreach ($quans as $quan) {
            if (!$quan->TenQuan) {
                continue;
            }

            $normalizedTenQuan = $normalize($quan->TenQuan);
            if ($normalizedTenQuan !== '' &&
                mb_stripos($normalizedCandidate, $normalizedTenQuan) !== false) {
                return $quan;
            }

            if (mb_stripos($address, $quan->TenQuan) !== false) {
                return $quan;
            }
        }

        return null;
    }
}
