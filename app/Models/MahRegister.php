<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MahRegister extends Model
{
    use HasFactory;

    protected $fillable = [
        'mah_id',
        'hazard_category',
        'major_accident_hazard',
        'cause',
        'top_event',
        'consequences',
        'initial_risk',
        'residual_risk',
        'final_risk', // Pastikan final_risk ada
        'preventive_barriers',
        'mitigative_barriers',
        'rekomendasi',
        'referensi_sudi',
        'overall_status',
    ];

    /**
     * Relasi ke RiskControl.
     */
    public function riskControls()
    {
        return $this->hasMany(RiskControl::class);
    }

    /**
     * (LOGIKA DIPERBARUI) Fungsi untuk menghitung ulang status keseluruhan.
     */
    public function updateOverallStatus()
    {
        // 1. Ambil semua 'anak' risk control dan hitung jumlahnya
        $controls = $this->riskControls()->get(); // Ambil collection
        $totalControls = $controls->count();

        // 2. Tentukan Status Baru
        $newStatus = 'OPEN'; // Default jika tidak ada anak

        if ($totalControls == 1) {
            // --- Logika Baru Jika Hanya Ada 1 Action Plan ---
            $control = $controls->first(); // Ambil satu-satunya action plan
            $checkedCount = 0;
            if ($control->eng) $checkedCount++;
            if ($control->proc) $checkedCount++;
            if ($control->cons) $checkedCount++;
            if ($control->comm) $checkedCount++;

            if ($checkedCount == 4) {
                $newStatus = 'CLOSE';
            } elseif ($checkedCount > 0) { // Jika 1, 2, atau 3 tercentang
                $newStatus = 'ON PROGRESS';
            } else { // Jika 0 tercentang
                $newStatus = 'OPEN';
            }
            // --- Akhir Logika Baru ---

        } elseif ($totalControls > 1) {
            // --- Logika Lama Jika Ada Lebih Dari 1 Action Plan ---
            $closedControls = $controls->where('action_status', 'CLOSE')->count();
            $openControls = $controls->where('action_status', 'OPEN')->count();
            // Status default jika > 1 anak adalah ON PROGRESS
            $newStatus = 'ON PROGRESS';

            if ($closedControls == $totalControls) {
                $newStatus = 'CLOSE';
            } elseif ($openControls == $totalControls) {
                $newStatus = 'OPEN';
            }
            // Jika tidak semua OPEN atau tidak semua CLOSE, status tetap ON PROGRESS
            // --- Akhir Logika Lama ---
        }
        // Jika $totalControls == 0, status tetap default 'OPEN'

        // 3. Simpan status baru ke database
        if ($this->overall_status != $newStatus) { // Hanya simpan jika ada perubahan
            $this->overall_status = $newStatus;
            $this->save();
        }
    }
}
