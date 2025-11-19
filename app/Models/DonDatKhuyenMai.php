<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DonDatKhuyenMai extends Model
{
    protected $table = 'DonDat_KhuyenMai';
    public $incrementing = false;
    public $timestamps = false;

    protected $fillable = [
        'ID_DD',
        'ID_KM',
        'TienGiam',
    ];
}

