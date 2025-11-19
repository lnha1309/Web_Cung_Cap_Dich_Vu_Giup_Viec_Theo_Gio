<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;

class TaiKhoan extends Authenticatable
{
    use HasFactory;

    protected $table = 'TaiKhoan';
    protected $primaryKey = 'ID_TK';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'ID_TK',
        'TenDN',
        'MatKhau',
        'ID_LoaiTK',
        'TrangThaiTK',
    ];

    protected $hidden = [
        'MatKhau',
    ];

    public $timestamps = false;

    public function getAuthPassword()
    {
        return $this->MatKhau;
    }

    public function khachHang()
    {
        return $this->hasOne(KhachHang::class, 'ID_TK', 'ID_TK');
    }

    public function nhanVien()
    {
        return $this->hasOne(NhanVien::class, 'ID_TK', 'ID_TK');
    }

    public function getDisplayNameAttribute(): string
    {
        if ($this->khachHang) {
            return $this->khachHang->Ten_KH;
        }

        if ($this->nhanVien) {
            return $this->nhanVien->Ten_NV;
        }

        return $this->TenDN;
    }
}
