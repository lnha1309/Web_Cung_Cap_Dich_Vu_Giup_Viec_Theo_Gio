<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DanhGiaNhanVien extends Model
{
    protected $table = 'DanhGiaNhanVien';
    protected $primaryKey = 'ID_DG';
    public $incrementing = false;
    protected $keyType = 'string';
    public $timestamps = false;

    protected $fillable = [
        'ID_DG',
        'ID_DD',
        'ID_NV',
        'ID_KH',
        'Diem',
        'NhanXet',
        'ThoiGian',
    ];
}

