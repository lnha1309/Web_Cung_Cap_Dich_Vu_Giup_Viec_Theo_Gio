<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GoiThang extends Model
{
    protected $table = 'GoiThang';
    protected $primaryKey = 'ID_Goi';
    public $incrementing = false;
    protected $keyType = 'string';
    public $timestamps = false;

    protected $fillable = [
        'ID_Goi',
        'TenGoi',
        'SoNgay',
        'PhanTramGiam',
        'Mota',
    ];
}
