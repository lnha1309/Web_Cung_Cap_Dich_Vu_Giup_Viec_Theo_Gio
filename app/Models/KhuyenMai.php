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
        'is_delete',
    ];

    protected $casts = [
        'is_delete' => 'boolean',
    ];

    /**
     * Scope để lọc các khuyến mãi chưa bị xoá mềm
     */
    public function scopeNotDeleted($query)
    {
        return $query->where('is_delete', false);
    }
}
