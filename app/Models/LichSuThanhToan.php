<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LichSuThanhToan extends Model
{
    protected $table = 'LichSuThanhToan';
    protected $primaryKey = 'ID_LSTT';
    public $incrementing = false;
    protected $keyType = 'string';
    public $timestamps = false;

    protected $fillable = [
        'ID_LSTT',
        'PhuongThucThanhToan',
        'TrangThai',
        'ThoiGian',
        'SoTienThanhToan',
        'MaGiaoDichVNPAY',
        'ID_DD',
        'LoaiGiaoDich',
        'LyDoHoanTien',
        'MaGiaoDichGoc',
        'GhiChu', // REQUIRED: Without this, GhiChu will NOT be saved!
    ];
}
