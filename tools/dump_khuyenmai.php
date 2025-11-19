<?php

// Simple helper script to inspect promotion data in the SQLite database.

$dbPath = __DIR__ . '/../database/database.sqlite';

if (!file_exists($dbPath)) {
    fwrite(STDERR, "Database file not found: {$dbPath}" . PHP_EOL);
    exit(1);
}

$pdo = new PDO('sqlite:' . $dbPath);
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

$stmt = $pdo->query('SELECT ID_KM, Ten_KM, PhanTramGiam, GiamToiDa, NgayBatDau, NgayKetThuc, TrangThai FROM KhuyenMai');

while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    echo implode(' | ', [
        $row['ID_KM'],
        $row['Ten_KM'],
        $row['PhanTramGiam'],
        $row['GiamToiDa'],
        $row['NgayBatDau'],
        $row['NgayKetThuc'],
        $row['TrangThai'] ?? '',
    ]) . PHP_EOL;
}

