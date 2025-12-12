<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PhuThu extends Model
{
    protected $table = 'PhuThu';
    protected $primaryKey = 'ID_PT';
    public $incrementing = false;
    protected $keyType = 'string';
    public $timestamps = false;

    protected $fillable = [
        'ID_PT',
        'Ten_PT',
        'GiaCuoc',
        'is_delete',
    ];

    protected $casts = [
        'GiaCuoc' => 'decimal:2',
        'is_delete' => 'boolean',
    ];

    /**
     * Scope để lọc các phụ thu chưa bị xoá mềm
     */
    public function scopeNotDeleted($query)
    {
        return $query->where('is_delete', false);
    }
}
