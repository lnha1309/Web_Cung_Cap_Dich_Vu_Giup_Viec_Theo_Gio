<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ChiTietPhuThu extends Model
{
    protected $table = 'ChiTietPhuThu';
    public $incrementing = false;
    public $timestamps = false;

    protected $fillable = [
        'ID_PT',
        'ID_DD',
        'Ghichu',
    ];

    public function phuThu()
    {
        return $this->belongsTo(PhuThu::class, 'ID_PT', 'ID_PT');
    }

    public function donDat()
    {
        return $this->belongsTo(DonDat::class, 'ID_DD', 'ID_DD');
    }
}
