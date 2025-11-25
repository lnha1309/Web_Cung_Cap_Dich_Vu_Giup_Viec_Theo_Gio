<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Support\Facades\Hash;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Notifications\Notifiable;

class TaiKhoan extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

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
        'name',
        'email',
        'onesignal_player_id',
    ];

    protected $hidden = [
        'MatKhau',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    public function setMatKhauAttribute(string $value): void
    {
        // Hash passwords on assignment while avoiding double hashing
        $this->attributes['MatKhau'] = Hash::needsRehash($value)
            ? Hash::make($value)
            : $value;
    }

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
