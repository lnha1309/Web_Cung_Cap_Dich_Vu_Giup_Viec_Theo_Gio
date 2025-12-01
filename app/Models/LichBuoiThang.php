<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LichBuoiThang extends Model
{
    protected $table = 'LichBuoiThang';
    protected $primaryKey = 'ID_Buoi';
    public $incrementing = false;
    protected $keyType = 'string';
    public $timestamps = false;

    protected $fillable = [
        'ID_Buoi',
        'ID_DD',
        'NgayLam',
        'GioBatDau',
        'TrangThaiBuoi',
        'ID_NV',
    ];

    public function donDat()
    {
        return $this->belongsTo(DonDat::class, 'ID_DD', 'ID_DD');
    }
}
