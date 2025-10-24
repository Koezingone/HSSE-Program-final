<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MahRegister extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'mah_id',
        'hazard_category',
        'major_accident_hazard',
        'cause',
        'top_event',
        'consequences',
        'initial_risk',
        'preventive_barriers',
        'mitigative_barriers',
        'residual_risk',
        'final_risk',
        'rekomendasi',
        'referensi_sudi',
        'overall_status',
    ];

    /**
     * Mendapatkan semua risk control yang dimiliki oleh MAH Register.
     */
    public function riskControls()
    {
        return $this->hasMany(RiskControl::class);
    }

    public function updateOverallStatus()
    {
        // 1. Ambil semua 'anak' risk control
        $totalControls = $this->riskControls()->count();

        // Jika tidak punya 'anak', statusnya OPEN
        if ($totalControls == 0) {
            $this->overall_status = 'OPEN';
            $this->save();
            return;
        }

        // 2. Hitung jumlah status
        $closedControls = $this->riskControls()->where('action_status', 'CLOSE')->count();
        $openControls = $this->riskControls()->where('action_status', 'OPEN')->count();

        // 3. Terapkan Logika Anda
        $newStatus = 'ON PROGRESS'; // Asumsi default adalah ON PROGRESS (campur-campur)

        if ($closedControls == $totalControls) {
            // Rule 1: Jika semua anak 'CLOSE', status induk jadi 'CLOSE'
            $newStatus = 'CLOSE';
        } elseif ($openControls == $totalControls) {
            // Rule 2: Jika semua anak 'OPEN', status induk jadi 'OPEN'
            $newStatus = 'OPEN';
        }
        // Rule 3: Jika tidak (ada yang CLOSE, ada yang OPEN), status tetap 'ON PROGRESS'

        // 4. Simpan status baru ke database
        $this->overall_status = $newStatus;
        $this->save();
    }
}
