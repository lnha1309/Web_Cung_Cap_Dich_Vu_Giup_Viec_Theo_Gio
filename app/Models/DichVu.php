<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DichVu extends Model
{
    protected $table = 'DichVu';
    protected $primaryKey = 'ID_DV';
    public $incrementing = false;
    protected $keyType = 'string';
    public $timestamps = false;

    protected $fillable = [
        'ID_DV',
        'TenDV',
        'MoTa',
        'GiaDV',
        'DienTichToiDa',
        'SoPhong',
        'ThoiLuong',
    ];
}

