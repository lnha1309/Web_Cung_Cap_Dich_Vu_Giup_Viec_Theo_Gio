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
    ];

    protected $casts = [
        'GiaCuoc' => 'decimal:2',
    ];
}
