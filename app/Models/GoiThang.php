<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GoiThang extends Model
{
    protected $table = 'GoiThang';
    protected $primaryKey = 'ID_Goi';
    public $incrementing = false;
    protected $keyType = 'string';
    public $timestamps = false;

    protected $fillable = [
        'ID_Goi',
        'TenGoi',
        'SoNgay',
        'PhanTramGiam',
        'Mota',
        'is_delete',
    ];

    protected $casts = [
        'is_delete' => 'boolean',
    ];

    /**
     * Scope để lọc các gói tháng chưa bị xoá mềm
     */
    public function scopeNotDeleted($query)
    {
        return $query->where('is_delete', false);
    }
}
