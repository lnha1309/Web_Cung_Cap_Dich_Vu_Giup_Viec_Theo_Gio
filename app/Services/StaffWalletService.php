<?php

namespace App\Services;

use App\Models\LichSuViNhanVien;
use App\Models\NhanVien;
use App\Support\IdGenerator;
use Illuminate\Support\Facades\DB;

class StaffWalletService
{
    public const MIN_BALANCE = 400000;

    public function balance(NhanVien $staff): float
    {
        return (float) ($staff->SoDu ?? 0);
    }

    public function canReceiveOrders(NhanVien $staff): bool
    {
        return $this->balance($staff) >= self::MIN_BALANCE;
    }

    public function summary(NhanVien $staff): array
    {
        $balance = $this->balance($staff);

        return [
            'balance' => $balance,
            'wallet_balance' => $balance,
            'min_balance_required' => self::MIN_BALANCE,
            'can_receive_orders' => $balance > self::MIN_BALANCE,
        ];
    }

    public function applyChange(NhanVien $staff, float $amount, string $type, array $meta = []): LichSuViNhanVien
    {
        return DB::transaction(function () use ($staff, $amount, $type, $meta) {
            $lockedStaff = NhanVien::where('ID_NV', $staff->ID_NV)
                ->lockForUpdate()
                ->firstOrFail();

            $currentBalance = (float) ($lockedStaff->SoDu ?? 0);
            $newBalance = $currentBalance + $amount;

            $historyId = IdGenerator::next('LichSuViNhanVien', 'ID_LSV', 'LSV_');

            $history = LichSuViNhanVien::create([
                'ID_LSV' => $historyId,
                'ID_NV' => $lockedStaff->ID_NV,
                'LoaiGiaoDich' => $type,
                'Huong' => $amount >= 0 ? 'in' : 'out',
                'SoTien' => $amount,
                'SoDuSau' => $newBalance,
                'MoTa' => $meta['description'] ?? null,
                'TrangThai' => $meta['status'] ?? 'success',
                'ID_DD' => $meta['order_id'] ?? null,
                'Nguon' => $meta['source'] ?? null,
                'MaThamChieu' => $meta['reference'] ?? null,
                'MaGiaoDich' => $meta['transaction_no'] ?? null,
            ]);

            $lockedStaff->SoDu = $newBalance;
            $lockedStaff->save();

            return $history;
        });
    }

    public function createPendingTopup(NhanVien $staff, float $amount, string $reference, array $meta = []): LichSuViNhanVien
    {
        $historyId = IdGenerator::next('LichSuViNhanVien', 'ID_LSV', 'LSV_');

        return LichSuViNhanVien::create([
            'ID_LSV' => $historyId,
            'ID_NV' => $staff->ID_NV,
            'LoaiGiaoDich' => $meta['type'] ?? 'topup',
            'Huong' => 'in',
            'SoTien' => $amount,
            'SoDuSau' => null,
            'MoTa' => $meta['description'] ?? null,
            'TrangThai' => 'pending',
            'Nguon' => $meta['source'] ?? 'vnpay',
            'MaThamChieu' => $reference,
        ]);
    }

    public function finalizeTopup(string $reference, bool $success, ?string $transactionNo = null, ?string $responseCode = null): ?LichSuViNhanVien
    {
        return DB::transaction(function () use ($reference, $success, $transactionNo, $responseCode) {
            $history = LichSuViNhanVien::where('MaThamChieu', $reference)
                ->lockForUpdate()
                ->orderByDesc('created_at')
                ->first();

            if (!$history) {
                return null;
            }

            if ($history->TrangThai !== 'pending') {
                return $history;
            }

            if (!$success) {
                $history->TrangThai = 'failed';
                $history->MaGiaoDich = $transactionNo;
                $history->MoTa = trim(($history->MoTa ?? '') . ' (ma ' . ($responseCode ?? 'unk') . ')');
                $history->save();
                return $history;
            }

            $staff = NhanVien::where('ID_NV', $history->ID_NV)
                ->lockForUpdate()
                ->first();

            if (!$staff) {
                $history->TrangThai = 'failed';
                $history->save();
                return $history;
            }

            $currentBalance = (float) ($staff->SoDu ?? 0);
            $newBalance = $currentBalance + (float) $history->SoTien;

            $history->TrangThai = 'success';
            $history->SoDuSau = $newBalance;
            $history->MaGiaoDich = $transactionNo;
            $history->save();

            $staff->SoDu = $newBalance;
            $staff->save();

            return $history;
        });
    }

    public function hasOrderTransaction(string $orderId, array $types): bool
    {
        return LichSuViNhanVien::where('ID_DD', $orderId)
            ->whereIn('LoaiGiaoDich', $types)
            ->exists();
    }
}
