<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LichLamViec extends Model
{
    protected $table = 'LichLamViec';
    protected $primaryKey = 'ID_Lich';
    public $incrementing = false;
    protected $keyType = 'string';
    public $timestamps = false;

    protected $fillable = [
        'ID_Lich',
        'ID_NV',
        'NgayLam',
        'GioBatDau',
        'GioKetThuc',
        'TrangThai',
    ];

    public function nhanVien()
    {
        return $this->belongsTo(NhanVien::class, 'ID_NV', 'ID_NV');
    }
}

