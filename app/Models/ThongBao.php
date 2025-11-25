<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ThongBao extends Model
{
    public $timestamps = false;
    protected $table = 'ThongBao';
    protected $primaryKey = 'ID_TB';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'ID_TB',
        'ID_KH',
        'TieuDe',
        'NoiDung',
        'LoaiThongBao',
        'DaDoc',
        'ThoiGian',
        'DuLieuLienQuan',
    ];

    protected $casts = [
        'DaDoc' => 'boolean',
        'ThoiGian' => 'datetime',
        'DuLieuLienQuan' => 'array',
    ];

    /**
     * Relationship: ThongBao belongs to KhachHang
     */
    public function khachHang()
    {
        return $this->belongsTo(KhachHang::class, 'ID_KH', 'ID_KH');
    }

    /**
     * Scope: Only unread notifications
     */
    public function scopeUnread($query)
    {
        return $query->where('DaDoc', false);
    }

    /**
     * Scope: Order by newest first
     */
    public function scopeNewest($query)
    {
        return $query->orderBy('ThoiGian', 'desc');
    }

    /**
     * Scope: Filter by type
     */
    public function scopeOfType($query, $type)
    {
        return $query->where('LoaiThongBao', $type);
    }
}
