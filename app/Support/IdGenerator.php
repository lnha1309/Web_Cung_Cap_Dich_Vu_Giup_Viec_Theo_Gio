<?php

namespace App\Support;

use Illuminate\Support\Facades\DB;

class IdGenerator
{
    /**
     * Generate next incremental string ID with given prefix.
     *
     * Example: prefix "KH_" -> "KH_1", "KH_2", ...
     */
    public static function next(string $table, string $column, string $prefix): string
    {
        $like = $prefix . '%';

        $values = DB::table($table)
            ->where($column, 'like', $like)
            ->pluck($column);

        $max = 0;

        foreach ($values as $value) {
            $numberPart = (int) substr((string) $value, strlen($prefix));
            if ($numberPart > $max) {
                $max = $numberPart;
            }
        }

        $next = $max + 1;

        return $prefix . $next;
    }
}

