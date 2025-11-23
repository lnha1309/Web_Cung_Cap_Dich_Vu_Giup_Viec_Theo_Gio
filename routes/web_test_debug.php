<?php

use App\Models\LichLamViec;
use Carbon\Carbon;
use Illuminate\Support\Facades\Route;

Route::get('/debug/find-staff', function () {
    $ngayLam = '2025-11-25'; // Thay bằng ngày bạn test
    $gioBatDau = '07:30';     // Thời gian bạn test
    $thoiLuong = 2;            // Thời lượng 2 giờ

    $start = Carbon::createFromFormat('H:i', $gioBatDau);
    $gioKetThuc = $start->copy()->addHours($thoiLuong)->format('H:i:s');
    $gioBatDauSql = $start->format('H:i:s');

    echo "<h2>Debug Find Staff</h2>";
    echo "<p><strong>Ngày làm:</strong> $ngayLam</p>";
    echo "<p><strong>Giờ bắt đầu (input):</strong> $gioBatDau</p>";
    echo "<p><strong>Giờ bắt đầu (SQL):</strong> $gioBatDauSql</p>";
    echo "<p><strong>Thời lượng:</strong> $thoiLuong giờ</p>";
    echo "<p><strong>Giờ kết thúc (calculated):</strong> $gioKetThuc</p>";

    echo "<hr>";
    echo "<h3>1. Tất cả lịch làm việc trong ngày (không filter):</h3>";
    $allLich = LichLamViec::with('nhanVien')
        ->where('NgayLam', $ngayLam)
        ->get();

    if ($allLich->isEmpty()) {
        echo "<p style='color: red;'>❌ KHÔNG có lịch làm việc nào trong ngày $ngayLam</p>";
    } else {
        echo "<table border='1' cellpadding='5'>";
        echo "<tr><th>ID_Lich</th><th>ID_NV</th><th>Tên NV</th><th>NgayLam</th><th>GioBatDau</th><th>GioKetThuc</th><th>TrangThai</th></tr>";
        foreach ($allLich as $item) {
            echo "<tr>";
            echo "<td>{$item->ID_Lich}</td>";
            echo "<td>{$item->ID_NV}</td>";
            echo "<td>" . ($item->nhanVien->Ten_NV ?? 'N/A') . "</td>";
            echo "<td>{$item->NgayLam}</td>";
            echo "<td>{$item->GioBatDau}</td>";
            echo "<td>{$item->GioKetThuc}</td>";
            echo "<td>{$item->TrangThai}</td>";
            echo "</tr>";
        }
        echo "</table>";
    }

    echo "<hr>";
    echo "<h3>2. Lịch với TrangThai = 'ready':</h3>";
    $readyLich = LichLamViec::with('nhanVien')
        ->where('NgayLam', $ngayLam)
        ->where('TrangThai', 'ready')
        ->get();

    if ($readyLich->isEmpty()) {
        echo "<p style='color: red;'>❌ KHÔNG có lịch nào với TrangThai = 'ready'</p>";
    } else {
        echo "<table border='1' cellpadding='5'>";
        echo "<tr><th>ID_Lich</th><th>ID_NV</th><th>Tên NV</th><th>GioBatDau</th><th>GioKetThuc</th><th>Check GioBatDau</th><th>Check GioKetThuc</th></tr>";
        foreach ($readyLich as $item) {
            $checkStart = $item->GioBatDau <= $gioBatDauSql ? '✅' : '❌';
            $checkEnd = $item->GioKetThuc >= $gioKetThuc ? '✅' : '❌';
            
            echo "<tr>";
            echo "<td>{$item->ID_Lich}</td>";
            echo "<td>{$item->ID_NV}</td>";
            echo "<td>" . ($item->nhanVien->Ten_NV ?? 'N/A') . "</td>";
            echo "<td>{$item->GioBatDau}</td>";
            echo "<td>{$item->GioKetThuc}</td>";
            echo "<td>{$checkStart} ({$item->GioBatDau} <= {$gioBatDauSql})</td>";
            echo "<td>{$checkEnd} ({$item->GioKetThuc} >= {$gioKetThuc})</td>";
            echo "</tr>";
        }
        echo "</table>";
    }

    echo "<hr>";
    echo "<h3>3. Lịch PHÙ HỢP (với cả 2 điều kiện):</h3>";
    $matchedLich = LichLamViec::with('nhanVien')
        ->where('NgayLam', $ngayLam)
        ->where('TrangThai', 'ready')
        ->where('GioBatDau', '<=', $gioBatDauSql)
        ->where('GioKetThuc', '>=', $gioKetThuc)
        ->get();

    if ($matchedLich->isEmpty()) {
        echo "<p style='color: red;'>❌ KHÔNG tìm thấy nhân viên phù hợp</p>";
    } else {
        echo "<p style='color: green;'>✅ Tìm thấy {$matchedLich->count()} nhân viên phù hợp:</p>";
        echo "<table border='1' cellpadding='5'>";
        echo "<tr><th>ID_NV</th><th>Tên NV</th><th>GioBatDau</th><th>GioKetThuc</th></tr>";
        foreach ($matchedLich as $item) {
            echo "<tr>";
            echo "<td>{$item->ID_NV}</td>";
            echo "<td>" . ($item->nhanVien->Ten_NV ?? 'N/A') . "</td>";
            echo "<td>{$item->GioBatDau}</td>";
            echo "<td>{$item->GioKetThuc}</td>";
            echo "</tr>";
        }
        echo "</table>";
    }

    echo "<hr>";
    echo "<h3>4. TEST với giờ bắt đầu = 07:00:</h3>";
    $test2GioBatDau = '09:30';
    $test2Start = Carbon::createFromFormat('H:i', $test2GioBatDau);
    $test2GioKetThuc = $test2Start->copy()->addHours($thoiLuong)->format('H:i:s');
    $test2GioBatDauSql = $test2Start->format('H:i:s');

    echo "<p><strong>Giờ bắt đầu:</strong> $test2GioBatDauSql | <strong>Giờ kết thúc:</strong> $test2GioKetThuc</p>";

    $test2MatchedLich = LichLamViec::with('nhanVien')
        ->where('NgayLam', $ngayLam)
        ->where('TrangThai', 'ready')
        ->where('GioBatDau', '<=', $test2GioBatDauSql)
        ->where('GioKetThuc', '>=', $test2GioKetThuc)
        ->get();

    if ($test2MatchedLich->isEmpty()) {
        echo "<p style='color: red;'>❌ KHÔNG tìm thấy nhân viên phù hợp với 07:00</p>";
    } else {
        echo "<p style='color: green;'>✅ Tìm thấy {$test2MatchedLich->count()} nhân viên phù hợp với 07:00:</p>";
        echo "<table border='1' cellpadding='5'>";
        echo "<tr><th>ID_NV</th><th>Tên NV</th><th>GioBatDau</th><th>GioKetThuc</th></tr>";
        foreach ($test2MatchedLich as $item) {
            echo "<tr>";
            echo "<td>{$item->ID_NV}</td>";
            echo "<td>" . ($item->nhanVien->Ten_NV ?? 'N/A') . "</td>";
            echo "<td>{$item->GioBatDau}</td>";
            echo "<td>{$item->GioKetThuc}</td>";
            echo "</tr>";
        }
        echo "</table>";
    }
});
