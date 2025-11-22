<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LichTheoTuan extends Model
{
    protected $table = 'LichTheoTuan';
    protected $primaryKey = 'ID_LichTuan';
    public $incrementing = false;
    protected $keyType = 'string';
    public $timestamps = false;

    protected $fillable = [
        'ID_LichTuan',
        'ID_DD',
        'Thu',
        'GioBatDau',
    ];
}
