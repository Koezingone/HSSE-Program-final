<?php

namespace App\Http\Controllers;

use App\Models\RiskControl;
use App\Models\MahRegister;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon; // <-- Tambahkan ini untuk manajemen tanggal

class RiskControlController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $risk_controls = RiskControl::with('mahRegister')->get();
        return view('risk-control.index', ['risk_controls' => $risk_controls]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $mah_registers = MahRegister::orderBy('mah_id')->get();
        $dropdowns = $this->getDropdownData();
        return view('risk-control.create', [
            'mah_registers' => $mah_registers,
            'locations' => $dropdowns['locations'],
            // 'action_statuses' dan 'final_risks' tidak diperlukan lagi di form 'create'
            // karena sudah otomatis
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'mah_register_id' => 'required|exists:mah_registers,id',
            'action_plan' => 'required',
        ]);

        $data = $request->all();

        // --- (LOGIKA BARU ANDA) ---
        // 1. Hitung checklist
        $checkedCount = 0;
        $data['eng']  = $request->has('eng') ? 1 : 0;
        $data['proc'] = $request->has('proc') ? 1 : 0;
        $data['cons'] = $request->has('cons') ? 1 : 0;
        $data['comm'] = $request->has('comm') ? 1 : 0;

        if ($data['eng']) $checkedCount++;
        if ($data['proc']) $checkedCount++;
        if ($data['cons']) $checkedCount++;
        if ($data['comm']) $checkedCount++;

        // 2. Atur Persentase (Rule 3)
        $persentase = $checkedCount * 25; // 0, 25, 50, 75, 100
        $data['persentase'] = $persentase . '%';

        // 3. Atur Status & Tanggal Selesai (Rule 2 & 4)
        if ($checkedCount == 4) {
            // Jika 4/4 tercentang (100%)
            $data['action_status'] = 'CLOSE';
            $data['actual_complete_date'] = Carbon::now()->toDateString(); // Tanggal hari ini
        } else {
            // Jika 0, 1, 2, atau 3 tercentang
            $data['action_status'] = 'OPEN';
            $data['actual_complete_date'] = null; // Kosongkan tanggal selesai
        }

        // 4. HAPUS LOGIKA LAMA
        // Logika "if (!empty($data['actual_complete_date']))" sudah tidak berlaku.
        // --- (AKHIR LOGIKA BARU) ---

        $riskControl = RiskControl::create($data);

        // Panggil logika "Overall Status"
        if ($riskControl->mahRegister) {
            $riskControl->mahRegister->updateOverallStatus();
        }
        // --- AKHIR ---

        return redirect()->route('risk-control.index')
            ->with('status', 'Data Risk Control baru berhasil disimpan!');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(RiskControl $riskControl)
    {
        $mah_registers = MahRegister::orderBy('mah_id')->get();
        $dropdowns = $this->getDropdownData();
        return view('risk-control.edit', [
            'risk_control' => $riskControl,
            'mah_registers' => $mah_registers,
            'locations' => $dropdowns['locations'],
            // Kita tidak perlu mengirim 'action_statuses' atau 'final_risks'
            // karena nilainya akan ditampilkan 'readonly'
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, RiskControl $riskControl)
    {
        $request->validate([
            'mah_register_id' => 'required|exists:mah_registers,id',
            'action_plan' => 'required',
        ]);

        $data = $request->all();

        // --- (LOGIKA BARU ANDA) ---
        // 1. Hitung checklist
        $checkedCount = 0;
        $data['eng']  = $request->has('eng') ? 1 : 0;
        $data['proc'] = $request->has('proc') ? 1 : 0;
        $data['cons'] = $request->has('cons') ? 1 : 0;
        $data['comm'] = $request->has('comm') ? 1 : 0;

        if ($data['eng']) $checkedCount++;
        if ($data['proc']) $checkedCount++;
        if ($data['cons']) $checkedCount++;
        if ($data['comm']) $checkedCount++;

        // 2. Atur Persentase (Rule 3)
        $persentase = $checkedCount * 25; // 0, 25, 50, 75, 100
        $data['persentase'] = $persentase . '%';

        // 3. Atur Status & Tanggal Selesai (Rule 2 & 4)
        if ($checkedCount == 4) {
            // Jika 4/4 tercentang (100%)
            $data['action_status'] = 'CLOSE';
            // Isi tanggal hanya jika sebelumnya kosong
            if (empty($riskControl->actual_complete_date)) {
                $data['actual_complete_date'] = Carbon::now()->toDateString();
            }
        } else {
            // Jika 0, 1, 2, atau 3 tercentang
            $data['action_status'] = 'OPEN';
            $data['actual_complete_date'] = null; // Kosongkan tanggal selesai
        }

        // 4. HAPUS LOGIKA LAMA
        // Logika "if (!empty($data['actual_complete_date']))" sudah tidak berlaku.
        // --- (AKHIR LOGIKA BARU) ---

        $riskControl->update($data);

        // Panggil logika "Overall Status"
        if ($riskControl->mahRegister) {
            $riskControl->mahRegister->updateOverallStatus();
        }
        // --- AKHIR ---

        return redirect()->route('risk-control.index')
            ->with('status', 'Data Risk Control berhasil di-update!');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(RiskControl $riskControl)
    {
        $mahRegister = $riskControl->mahRegister;
        $riskControl->delete();
        if ($mahRegister) {
            $mahRegister->updateOverallStatus();
        }
        return redirect()->route('risk-control.index')
            ->with('status', 'Data Risk Control berhasil dihapus!');
    }

    /**
     * Helper function untuk data dropdown
     */
    private function getDropdownData()
    {
        return [
            'locations' => [
                'Area IT Banjarmasin',
                'Area Dermaga',
                'Area Jetty 1',
                'Area Jetty 2',
                'Area Jetty 3',
                'Area Tank Yard',
                'Area Fillingshed',
                'Kantor',
                'Lab QQ',
                'Parkir Mobil Tangki',
                'Gudang Limbah B3',
                'Rumah Pompa Produk',
                'Rumah Pompa PMK',
                'Workshop',
                'Oil Catcher',
                'Control Room Depan',
                'Control Room Belakang',
                'Ruang Genset',
            ],
            // Kita tidak butuh 'action_statuses' dan 'final_risks' lagi di form
        ];
    }
}
