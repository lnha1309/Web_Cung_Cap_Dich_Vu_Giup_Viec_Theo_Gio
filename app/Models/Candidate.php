<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Candidate extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'phone',
        'email',
        'gender',
        'dob',
        'experience',
        'position',
        'address',
        'education',
        'cv_path',
        'cover_letter',
        'current_job',
        'type',
        'desired_salary',
        'feedback',
        'source',
        'status',
        'note',
        'applied_at',
    ];

    protected $casts = [
        'dob' => 'date',
        'applied_at' => 'date',
    ];
}
