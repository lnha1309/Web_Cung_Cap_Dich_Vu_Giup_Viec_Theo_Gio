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
        'is_delete',
    ];

    protected $casts = [
        'is_delete' => 'boolean',
    ];

    /**
     * Scope để lọc các dịch vụ chưa bị xoá mềm
     */
    public function scopeNotDeleted($query)
    {
        return $query->where('is_delete', false);
    }
}

