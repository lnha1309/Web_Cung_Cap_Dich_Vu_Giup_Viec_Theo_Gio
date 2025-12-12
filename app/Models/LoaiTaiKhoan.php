<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LoaiTaiKhoan extends Model
{
    protected $table = 'LoaiTaiKhoan';
    protected $primaryKey = 'ID_LoaiTK';
    public $incrementing = false;
    protected $keyType = 'string';
    public $timestamps = false;

    protected $fillable = [
        'ID_LoaiTK',
        'TenLoai',
    ];

    public function taiKhoans()
    {
        return $this->hasMany(TaiKhoan::class, 'ID_LoaiTK', 'ID_LoaiTK');
    }
}
