<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class KhuyenMai extends Model
{
    protected $table = 'KhuyenMai';
    protected $primaryKey = 'ID_KM';
    public $incrementing = false;
    protected $keyType = 'string';
    public $timestamps = false;

    protected $fillable = [
        'ID_KM',
        'Ten_KM',
        'MoTa',
        'PhanTramGiam',
        'GiamToiDa',
        'TrangThai',
        'NgayHetHan',
    ];
}
