<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RiskControl extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'mah_register_id',
        'action_plan',
        'action_progress',
        'location',
        'plan_complete_date',
        'actual_complete_date',
        'action_status',
        'referensi_sudi',
        'eng',
        'proc',
        'cons',
        'comm',
        'persentase',
    ];

    /**
     * (BARU) Beri tahu Laravel tipe data untuk kolom-kolom ini.
     * Ini akan mengubah 1/0 dari database menjadi true/false di kode.
     *
     * @var array
     */
    protected $casts = [
        'eng' => 'boolean',
        'proc' => 'boolean',
        'cons' => 'boolean',
        'comm' => 'boolean',
    ];

    /**
     * Mendapatkan MAH Register yang memiliki Risk Control ini.
     */
    public function mahRegister()
    {
        return $this->belongsTo(MahRegister::class);
    }
}
