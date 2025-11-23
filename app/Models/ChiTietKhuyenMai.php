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

    /**
     * Get the voucher (KhuyenMai) associated with this detail
     */
    public function khuyenMai()
    {
        return $this->belongsTo(KhuyenMai::class, 'ID_KM', 'ID_KM');
    }

    /**
     * Get the booking (DonDat) associated with this detail
     */
    public function donDat()
    {
        return $this->belongsTo(DonDat::class, 'ID_DD', 'ID_DD');
    }
}

