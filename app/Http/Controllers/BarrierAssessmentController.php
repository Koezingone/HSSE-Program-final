<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\BarrierAssessment;

class BarrierAssessmentController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $assessments = BarrierAssessment::orderBy('barrier_category')
            ->orderBy('specific_barrier')
            ->get();
        return view('barrier-assessments.index', ['assessments' => $assessments]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $barrierOptions = $this->getBarrierOptions();

        return view('barrier-assessments.create', [
            'barrierOptions' => $barrierOptions, // Data lengkap untuk JS
            'barrierCategories' => array_keys($barrierOptions), // Hanya nama kategori
        ]);
    }


    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'barrier_category' => 'required|string|max:255',
            'specific_barrier' => 'required|string|max:255',
            'percentage' => 'nullable|numeric|min:0|max:100|regex:/^\d+(\.\d{1,2})?$/', // Max 2 desimal
            'assessment_date' => 'nullable|date',
            'notes' => 'nullable|string',
        ]);

        // Cari tipe barrier berdasarkan input
        $options = $this->getBarrierOptions();
        $barrierType = null;
        $barrierFound = false;
        if (isset($options[$validatedData['barrier_category']])) {
            foreach ($options[$validatedData['barrier_category']] as $barrier) {
                if ($barrier['name'] === $validatedData['specific_barrier']) {
                    $barrierType = $barrier['type']; // Ambil tipe dari data options
                    $barrierFound = true;
                    break;
                }
            }
        }

        // Validasi tambahan jika barrier tidak cocok
        if (!$barrierFound) {
            return back()->withErrors(['specific_barrier' => 'Barrier spesifik tidak valid untuk kategori yang dipilih.'])->withInput();
        }

        // Tambahkan tipe barrier ke data yang akan disimpan
        $validatedData['barrier_type'] = $barrierType;

        BarrierAssessment::create($validatedData);

        return redirect()->route('barrier-assessments.index')
            ->with('status', 'Data Barrier Assessment berhasil disimpan!');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(BarrierAssessment $barrierAssessment)
    {
        $barrierOptions = $this->getBarrierOptions();
        return view('barrier-assessments.edit', [
            'assessment' => $barrierAssessment,
            'barrierOptions' => $barrierOptions,
            'barrierCategories' => array_keys($barrierOptions),
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        // Validasi input
        $validatedData = $request->validate([
            'barrier_category' => 'required|string|max:255',
            'specific_barrier' => 'required|string|max:255',
            'percentage' => 'nullable|numeric|min:0|max:100|regex:/^\d+(\.\d{1,2})?$/',
            'assessment_date' => 'nullable|date',
            'notes' => 'nullable|string',
        ]);

        // Ambil data lama dari database
        $assessment = BarrierAssessment::findOrFail($id);

        // Ambil barrier type berdasarkan kategori dan nama barrier
        $options = $this->getBarrierOptions();
        $barrierType = null;
        if (isset($options[$validatedData['barrier_category']])) {
            foreach ($options[$validatedData['barrier_category']] as $barrier) {
                if ($barrier['name'] === $validatedData['specific_barrier']) {
                    $barrierType = $barrier['type'];
                    break;
                }
            }
        }

        $validatedData['barrier_type'] = $barrierType;

        // Update ke database
        $assessment->update($validatedData);

        // Redirect kembali ke halaman index dengan pesan sukses
        return redirect()->route('barrier-assessments.index')
            ->with('status', 'Data Barrier Assessment berhasil diperbarui!');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        // Cari data berdasarkan ID
        $assessment = BarrierAssessment::findOrFail($id);

        // Hapus data dari database
        $assessment->delete();

        // Redirect kembali ke halaman index dengan pesan sukses
        return redirect()->route('barrier-assessments.index')
            ->with('status', 'Data Barrier Assessment berhasil dihapus!');
    }

    /**
     * Struktur barrier berdasarkan kategori utama
     */
    private function getBarrierOptions()
    {
        return [
            'Prevention' => [
                ['name' => 'SS-01 Tangki Produk', 'type' => 'Hardware'],
                ['name' => 'SS-02 Pipa Produk', 'type' => 'Hardware'],
                ['name' => 'RE-01 Pompa Produk', 'type' => 'Hardware'],
                ['name' => 'Program Periodically Inspection', 'type' => 'Human'],
                ['name' => 'Program Integrity Management / RBI', 'type' => 'Human'],
                ['name' => 'Pipeline Pigging Programme', 'type' => 'Human'],
                ['name' => 'Programme Condition Monitoring (for rotating Equipment) / LTSA', 'type' => 'Human'],
                ['name' => 'Housekeeping Programme', 'type' => 'Human'],
                ['name' => 'Pemeliharaan & Pergantian Valve/Flange/Gasket secara periodikal', 'type' => 'Human'],
                ['name' => 'Prosedur Penerimaan Produk', 'type' => 'Human'],
                ['name' => 'Prosedur Perbaikan / Pemeliharaan Peralatan', 'type' => 'Human'],
                ['name' => 'Prosedur Sistem Ijin Kerja Aman (SIKA)', 'type' => 'Human'],
                ['name' => 'Prosedur Management of Change (MOC)', 'type' => 'Human'],
                ['name' => 'Prosedur Isolation Energi (LOTO)', 'type' => 'Human'],
                ['name' => 'Authorized Personel (GSI/SI/AT)', 'type' => 'Human'],
                ['name' => 'Prosedur Ship Approching', 'type' => 'Human'],
                ['name' => 'Prosedur Ship Transfer', 'type' => 'Human'],
                ['name' => 'Pipeline ROW (Marking & signage)', 'type' => 'Human'],
                ['name' => 'Community Engagement di area Facility & ROW pipeline.', 'type' => 'Human'],
                ['name' => 'Penentuan Daerah Terbatas & Terlarang (restricted area declaration)', 'type' => 'Human'],
                ['name' => 'Prosedur Sampling', 'type' => 'Human'],
                ['name' => 'Program pembersihan marine organism secara periodik.', 'type' => 'Human'],
                ['name' => 'Maintenance Structural Support Programe.', 'type' => 'Human'],
                ['name' => 'Prosedur Pengisian Tangki Timbun', 'type' => 'Human'],
                ['name' => 'Prosedur Start Up & Shutdown', 'type' => 'Human'],
                ['name' => 'Program Tank Cleaning', 'type' => 'Human'],
                ['name' => 'Periodically Inspection Lightning Protection', 'type' => 'Human'],
                ['name' => 'Prosedur / Klausul Pengoperasian Tangki timbun Saat Cuaca Buruk', 'type' => 'Human'],
                ['name' => 'Prosedur pendistribusian / penyaluran Produk', 'type' => 'Human'],
            ],
            'Detect and Control' => [
                ['name' => 'DC-01 Level indicator (Level alarm / Level Alarm Switch)', 'type' => 'Hardware'],
                ['name' => 'DC-02/03 Actuated Isolation Valve (MOV)', 'type' => 'Hardware'],
                ['name' => 'MP-01 Overpressure Protection (PSV/PVRV/Vent)', 'type' => 'Hardware'],
                ['name' => 'DC-04 Power Supply', 'type' => 'Hardware'],
                ['name' => 'SP-03 Sumur Pantau', 'type' => 'Hardware'],
                ['name' => 'Prosedur Monitoring Level Tangki', 'type' => 'Human'],
                ['name' => 'Program Kalibrasi Level Indicator', 'type' => 'Human'],
                ['name' => 'Pipeline Leak Detectionr', 'type' => 'Human'],
                ['name' => 'Program analysis fugitive realease menggunakan fugitive imaging camera', 'type' => 'Human'],
                ['name' => 'Plant / Pipeline Patrol', 'type' => 'Human'],
            ],
            'Mitigation' => [
                ['name' => 'GD-01/02 Fixed Fire & Gas Detector', 'type' => 'Hardware'],
                ['name' => 'GD-03 Portable Gas Detector', 'type' => 'Hardware'],
                ['name' => 'DC-02 Actuated Intervention Valve (SDV)', 'type' => 'Hardware'],
                ['name' => 'IP-01 Electrical System', 'type' => 'Hardware'],
                ['name' => 'IP-02 Grounding System', 'type' => 'Hardware'],
                ['name' => 'IP-03 Lightning System', 'type' => 'Hardware'],
                ['name' => 'SP-01 Bundwall System', 'type' => 'Hardware'],
                ['name' => 'SP-02 Oil Catcher System', 'type' => 'Hardware'],
                ['name' => 'FP-07 Cooling System', 'type' => 'Hardware'],
                ['name' => 'Prosedur Pengukuran Grounding Secara Periodik', 'type' => 'Human'],
                ['name' => 'Electrical  Hazardous Area Classification (EHAC)', 'type' => 'Human'],
                ['name' => 'Security Guard', 'type' => 'Human'],
                ['name' => 'Pipeline ROW (Cleareance & maintenance).', 'type' => 'Human'],
                ['name' => 'Aturan jarak aman tangki (Tank Spacing)', 'type' => 'Human'],
                ['name' => 'Aturan Safety Distance', 'type' => 'Human'],
                ['name' => 'Prosedur Isolation dan Pengendalian Produk (Manual Operasi Intertank)', 'type' => 'Human'],
                ['name' => 'Prosedur Pengosongan Tanki', 'type' => 'Human'],
            ],
            'Emergency Response' => [
                ['name' => 'ER-01 Emergency Communication (HT)', 'type' => 'Hardware'],
                ['name' => 'ER-02 Muster Point', 'type' => 'Hardware'],
                ['name' => 'SP-04 Oil Spill Emergency Response', 'type' => 'Hardware'],
                ['name' => 'FP-04 Foam System', 'type' => 'Hardware'],
                ['name' => 'FP-01/02/03/05/06/10 Fixed Fire Fighting Equipment', 'type' => 'Hardware'],
                ['name' => 'FP-06 Mobile Fire Fighting Equipment', 'type' => 'Hardware'],
                ['name' => 'FP-10 Genset', 'type' => 'Hardware'],
                ['name' => 'Organisasi & Personel Pengendalian Tanggap Darurat yang Kompeten', 'type' => 'Human'],
                ['name' => 'Pedoman Pengendalian Keadaan Darurat', 'type' => 'Human'],
                ['name' => 'Bussiness Continuity Plan/Management', 'type' => 'Human'],
            ],
        ];
    }
}
