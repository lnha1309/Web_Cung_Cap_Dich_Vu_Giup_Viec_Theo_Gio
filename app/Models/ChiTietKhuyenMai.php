<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ChiTietKhuyenMai extends Model
{
    protected $table = 'ChiTietKhuyenMai';
    public $incrementing = false;
    public $timestamps = false;

    protected $fillable = [
        'ID_DD',
        'ID_KM',
        'TienGiam',
    ];
}

