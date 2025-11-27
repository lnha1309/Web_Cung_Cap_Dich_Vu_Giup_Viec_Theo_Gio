<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class NhanVien extends Model
{
    protected $table = 'NhanVien';
    protected $primaryKey = 'ID_NV';
    public $incrementing = false;
    protected $keyType = 'string';
    public $timestamps = false;

    protected $fillable = [
        'ID_NV',
        'Ten_NV',
        'ID_Quan',
        'NgaySinh',
        'GioiTinh',
        'SDT',
        'Email',
        'KhuVucLamViec',
        'HinhAnh',
        'SoDu',
        'TrangThai',
        'ID_TK',
    ];

    public function lichLamViecs()
    {
        return $this->hasMany(LichLamViec::class, 'ID_NV', 'ID_NV');
    }

    public function taiKhoan()
    {
        return $this->belongsTo(TaiKhoan::class, 'ID_TK', 'ID_TK');
    }
}
