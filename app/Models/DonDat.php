<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DonDat extends Model
{
    protected $table = 'DonDat';
    protected $primaryKey = 'ID_DD';
    public $incrementing = false;
    protected $keyType = 'string';
    public $timestamps = false;

    protected $fillable = [
        'ID_DD',
        'LoaiDon',
        'ID_DV',
        'ID_KH',
        'ID_DC',
        'GhiChu',
        'NgayTao',
        'NgayLam',
        'GioBatDau',
        'ThoiLuongGio',
        'ID_Goi',
        'NgayBatDauGoi',
        'NgayKetThucGoi',
        'TrangThaiDon',
        'TongTien',
        'TongTienSauGiam',
        'ID_NV',
        'ID_KM',
    ];
}

