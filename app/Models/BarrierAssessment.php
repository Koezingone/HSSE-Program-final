<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BarrierAssessment extends Model
{
    use HasFactory;

    protected $fillable = [
        'barrier_category',
        'specific_barrier',
        'barrier_type', // <-- Tambahkan ini
        'percentage',
        'assessment_date',
        'notes',
    ];
}
