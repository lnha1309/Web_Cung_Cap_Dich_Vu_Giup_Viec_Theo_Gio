<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Quan extends Model
{
    protected $table = 'Quan';
    protected $primaryKey = 'ID_Quan';
    public $incrementing = false;
    protected $keyType = 'string';
    public $timestamps = false;

    protected $fillable = [
        'ID_Quan',
        'TenQuan',
        'ViDo',
        'KinhDo',
    ];
}

