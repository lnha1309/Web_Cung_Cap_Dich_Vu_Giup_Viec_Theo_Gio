<?php

namespace App\Services;

use App\Models\PhuThu;
use Carbon\Carbon;

class SurchargeService
{
    /**
     * Detect applicable surcharges and return totals.
     *
     * @param string $loaiDon    hour|month
     * @param string|null $ngayLam  Y-m-d
     * @param string|null $gioBatDau H:i or H:i:s
     * @param array<int> $repeatDays Weekday numbers (0 = Sunday)
     * @param bool $hasPets
     * @param int $sessionCount Number of sessions (>=1). For hourly bookings, keep 1; for monthly, pass total sessions.
     * @param int|null $weekendSessionCount Number of sessions that fall on weekend (only needed for month)
     * @return array{ids:array<int,string>, total:float, items:array<int,array{id:string,amount:float,note:string}>}
     */
    public function calculate(
        string $loaiDon,
        ?string $ngayLam,
        ?string $gioBatDau,
        array $repeatDays = [],
        bool $hasPets = false,
        int $sessionCount = 1,
        ?int $weekendSessionCount = null
    ): array
    {
        $sessionCount = max(1, (int) $sessionCount);

        $ids = [];
        $weekendSessions = max(0, (int) ($weekendSessionCount ?? 0));

        if ($loaiDon === 'hour') {
            if ($ngayLam && $this->isWeekend($ngayLam)) {
                $ids[] = 'PT003';
                $weekendSessions = 1;
            }

            if ($gioBatDau && $this->isEarlyOrLate($gioBatDau)) {
                $ids[] = 'PT001';
            }
        } elseif ($loaiDon === 'month') {
            if (!empty(array_intersect($repeatDays, [0, 6]))) {
                $ids[] = 'PT003';
                if ($weekendSessions === 0) {
                    // Fallback: at least one weekend session if weekend is present but count not provided
                    $weekendSessions = 1;
                }
            }

            if ($gioBatDau && $this->isEarlyOrLate($gioBatDau)) {
                $ids[] = 'PT001';
            }
        }

        if ($hasPets) {
            $ids[] = 'PT002';
        }

        $ids = array_values(array_unique($ids));

        // Chỉ lấy phụ thu chưa bị xoá mềm
        $surcharges = PhuThu::whereIn('ID_PT', $ids)
            ->where('is_delete', false)
            ->get()
            ->keyBy('ID_PT');

        $items = [];
        $total = 0.0;

        foreach ($ids as $idPt) {
            $base = (float) ($surcharges->get($idPt)?->GiaCuoc ?? 0);
            if ($base <= 0) {
                continue;
            }

            $multiplier = 1;
            if ($idPt === 'PT003') {
                $multiplier = max(1, $weekendSessions);
            } else {
                $multiplier = $sessionCount;
            }

            $amount = $base * $multiplier;
            $items[] = [
                'id'           => $idPt,
                'label'        => $this->labelFor($idPt),
                'unit_amount'  => $base,
                'quantity'     => $multiplier,
                'amount'       => $amount,
                'note'         => $this->noteFor($idPt, $loaiDon, $multiplier),
            ];
            $total += $amount;
        }

        return [
            'ids'   => $ids,
            'total' => $total,
            'items' => $items,
        ];
    }

    private function isWeekend(string $date): bool
    {
        try {
            return Carbon::parse($date)->isWeekend();
        } catch (\Exception) {
            return false;
        }
    }

    private function isEarlyOrLate(string $time): bool
    {
        $carbon = $this->parseTime($time);
        if (!$carbon) {
            return false;
        }

        $hour = (int) $carbon->format('H');
        return $hour < 8 || $hour >= 17;
    }

    private function parseTime(string $time): ?Carbon
    {
        try {
            if (strlen($time) <= 5) {
                return Carbon::createFromFormat('H:i', $time);
            }

            return Carbon::createFromFormat('H:i:s', $time);
        } catch (\Exception) {
            return null;
        }
    }

    private function labelFor(string $idPt): string
    {
        return match ($idPt) {
            'PT003' => 'Phu thu cuoi tuan',
            'PT001' => 'Phu thu ngoai gio',
            'PT002' => 'Phu thu thu cung',
            default => 'Phu thu',
        };
    }

    private function noteFor(string $idPt, string $loaiDon, int $quantity): string
    {
        return match ($idPt) {
            'PT003' => $loaiDon === 'month'
                ? 'Goi thang co ' . $quantity . ' buoi cuoi tuan'
                : 'Lam 1 buoi vao cuoi tuan (T7/CN)',
            'PT001' => $loaiDon === 'month'
                ? 'Goi thang lam ngoai gio hanh chinh x' . $quantity . ' buoi'
                : 'Lam truoc 8h hoac sau 17h',
            'PT002' => 'Nha co thu cung x' . $quantity . ' buoi',
            default => '',
        };
    }
}
