<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class KhachHang extends Model
{
    protected $table = 'KhachHang';
    protected $primaryKey = 'ID_KH';
    public $incrementing = false;
    protected $keyType = 'string';
    public $timestamps = false;

    protected $fillable = [
        'ID_KH',
        'Ten_KH',
        'Email',
        'SDT',
        'ID_TK',
    ];

    public function diaChis()
    {
        return $this->hasMany(DiaChi::class, 'ID_KH', 'ID_KH');
    }
}
