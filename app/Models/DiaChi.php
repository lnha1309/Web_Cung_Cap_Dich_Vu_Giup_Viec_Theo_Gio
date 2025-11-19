<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DiaChi extends Model
{
    protected $table = 'DiaChi';
    protected $primaryKey = 'ID_DC';
    public $incrementing = false;
    protected $keyType = 'string';
    public $timestamps = false;

    protected $fillable = [
        'ID_DC',
        'ID_KH',
        'ID_Quan',
        'CanHo',
        'DiaChiDayDu',
        'is_Deleted',
    ];

    protected $casts = [
        'is_Deleted' => 'boolean',
    ];

    public function quan()
    {
        return $this->belongsTo(Quan::class, 'ID_Quan', 'ID_Quan');
    }
}
