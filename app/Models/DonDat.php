<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DonDat extends Model
{
    protected $table = 'DonDat';
    protected $primaryKey = 'ID_DD';
    public $incrementing = false;
    protected $keyType = 'string';
    public $timestamps = false;

    protected $fillable = [
        'ID_DD',
        'LoaiDon',
        'ID_DV',
        'ID_KH',
        'ID_DC',
        'GhiChu',
        'NgayTao',
        'NgayLam',
        'GioBatDau',
        'ThoiLuongGio',
        'ID_Goi',
        'NgayBatDauGoi',
        'NgayKetThucGoi',
        'TrangThaiDon',
        'TongTien',
        'TongTienSauGiam',
        'ID_NV',
        'ID_KM',
    ];

    /**
     * Relationship: Đơn đặt thuộc về dịch vụ
     */
    public function dichVu()
    {
        return $this->belongsTo(DichVu::class, 'ID_DV', 'ID_DV');
    }

    /**
     * Relationship: Đơn đặt có địa chỉ
     */
    public function diaChi()
    {
        return $this->belongsTo(DiaChi::class, 'ID_DC', 'ID_DC');
    }

    /**
     * Relationship: Đơn đặt thuộc về khách hàng
     */
    public function khachHang()
    {
        return $this->belongsTo(KhachHang::class, 'ID_KH', 'ID_KH');
    }

    /**
     * Relationship: Đơn đặt có các buổi làm việc (nếu là gói tháng)
     */
    public function lichBuoiThang()
    {
        return $this->hasMany(LichBuoiThang::class, 'ID_DD', 'ID_DD');
    }
}
