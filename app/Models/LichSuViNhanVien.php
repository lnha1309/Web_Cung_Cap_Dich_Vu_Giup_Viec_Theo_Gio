<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LichSuViNhanVien extends Model
{
    protected $table = 'LichSuViNhanVien';
    protected $primaryKey = 'ID_LSV';
    public $incrementing = false;
    protected $keyType = 'string';
    public $timestamps = true;

    protected $fillable = [
        'ID_LSV',
        'ID_NV',
        'LoaiGiaoDich',
        'Huong',
        'SoTien',
        'SoDuSau',
        'MoTa',
        'TrangThai',
        'ID_DD',
        'Nguon',
        'MaThamChieu',
        'MaGiaoDich',
    ];

    protected $casts = [
        'SoTien' => 'float',
        'SoDuSau' => 'float',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function nhanVien()
    {
        return $this->belongsTo(NhanVien::class, 'ID_NV', 'ID_NV');
    }
}
