<?php

namespace App\Http\Controllers;

use App\Models\MahRegister;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;

class MahRegisterController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $registers = MahRegister::all();
        return view('mah.index', ['registers' => $registers]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        // --- LOGIKA GENERATE ID OTOMATIS (BARU) ---
        $prefix = 'MAH-BJM-';
        $lastMah = MahRegister::where('mah_id', 'LIKE', $prefix . '%') // Cari yang cocok prefix
            ->orderBy('mah_id', 'desc') // Urutkan dari terbesar
            ->first(); // Ambil yang paling atas

        $nextNumber = 1; // Default jika belum ada data
        if ($lastMah) {
            // Ambil bagian angka dari ID terakhir (misal: '018' dari 'MAH-BJM-018')
            $lastNumber = (int) substr($lastMah->mah_id, strlen($prefix));
            $nextNumber = $lastNumber + 1;
        }

        // Format angka menjadi 3 digit dengan leading zero (misal: 1 -> '001', 19 -> '019')
        $nextMahId = $prefix . str_pad($nextNumber, 3, '0', STR_PAD_LEFT);
        // --- AKHIR LOGIKA GENERATE ID ---


        // Data untuk dropdown (tetap sama)
        $hazard_categories = $this->getHazardCategories();
        $causes = $this->getCauses();
        $top_events = $this->getTopEvents();

        // Kirim data ini ke view, termasuk ID baru
        return view('mah.create', [
            'hazard_categories' => $hazard_categories,
            'causes' => $causes,
            'top_events' => $top_events,
            'next_mah_id' => $nextMahId, // <-- Kirim ID baru ke view
        ]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        // Validasi bisa ditambahkan di sini
        // $request->validate([...]);

        MahRegister::create($request->all());

        return redirect()->route('mah-register.index')
                         ->with('status', 'Data MAH Register baru berhasil disimpan!');
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\MahRegister  $mahRegister
     * @return \Illuminate\Http\Response
     */
    public function show(MahRegister $mahRegister)
    {
        // Kita bisa buat halaman detail jika perlu,
        // tapi untuk sekarang kita langkahi ke edit
        return redirect()->route('mah-register.edit', $mahRegister->id);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\MahRegister  $mahRegister
     * @return \Illuminate\Http\Response
     */
    public function edit(MahRegister $mahRegister)
    {
        // Ambil data dropdown (sama seperti di create)
        $hazard_categories = $this->getHazardCategories();
        $causes = $this->getCauses();
        $top_events = $this->getTopEvents();

        // Kirim data register yang mau diedit DAN data dropdown ke view 'mah.edit'
        return view('mah.edit', [
            'register' => $mahRegister, // Data yang akan diedit
            'hazard_categories' => $hazard_categories,
            'causes' => $causes,
            'top_events' => $top_events,
        ]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\MahRegister  $mahRegister
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, MahRegister $mahRegister)
    {
        // Validasi bisa ditambahkan di sini
        // $request->validate([...]);

        // Update data di database
        $mahRegister->update($request->all());

        return redirect()->route('mah-register.index')
                         ->with('status', 'Data MAH Register berhasil di-update!');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\MahRegister  $mahRegister
     * @return \Illuminate\Http\Response
     */
    public function destroy(MahRegister $mahRegister)
    {
        // Hapus data dari database
        $mahRegister->delete();

        return redirect()->route('mah-register.index')
                         ->with('status', 'Data MAH Register berhasil dihapus!');
    }

    // --- HELPER METHODS UNTUK DROPDOWN ---
    // (Kita pindahkan ke sini agar tidak duplikat kode)

    private function getHazardCategories()
    {
        return [
            'H-01 Hydrocarbons', 'H-02 Refined Hydrocarbons', 'H-03 Other Flammable Materials',
            'H-04 Explosives', 'H-05 Pressure hazards', 'H-06 Hazards associated with differences in height',
            'H-07 Objects under induced stress', 'H-08 Dynamic situation hazards', 'H-09 Environmental hazard',
            'H-10 Hot Surfaces', 'H-11 Hot Fluids', 'H-12 Cold surfaces', 'H-14 Open Flame',
            'H-15 Electricity', 'H-19 Asphyxiates', 'H-20 Toxic Gas', 'H-21 Toxic Liquid',
            'H-22 Toxic Solids', 'H-23 Corrosive Substances', 'H-24 Biological Hazards', 'H-25 Other',
        ];
    }

    private function getCauses()
    {
        return [
            'Human Error', 'Equipment Failure', 'Process Deviations', 'External Events',
            'Poor Maintenance or Inspection', 'Inadequate Safety Management Systems', 'Design Deficiencies',
            'Organizational and Cultural Factors', 'Lack of Training and Competence', 'Change Management Failures',
            'Internal / Eksternal Corrosion', 'Failure at Valve, Flange, Seal & Small Bore',
            'Failure at Tank Bottom / Base Plate Metal & Support Structure (Spherical Tank)',
            'Operating Outside of Designated Operational Envelope', 'Maintenance Activity',
            'Third Party (Personel and/or Vehicle) Activities', 'Normal Operation Activity (Inc. Sampling Activity)',
            'Supporting Structure Failure', 'Bad / Extreem Wheather (High waves, windstorm, earthquake)',
            'Extreem Weather and/Or Natural Disaster', 'Other',
        ];
    }

    private function getTopEvents()
    {
        return [
            'Loss of containment (gas release)',
            'Toxic release to atmosphere',
            'Overpressure event / vessel rupture',
            'Spill in confined space',
            'Pipe rupture / leak',
            'Runaway chemical reaction',
            'Occupational illness from chronic chemical or ergonomic exposure',
            'Heat stress or asphyxiation',
            'Dropped object during lifting operations',
            'Fall from height',
            'Vehicle collision during logistics operations',
            'Sabotage or unauthorized access',
            'Theft of hazardous materials',
            'Cyberattack on safety instrumentation system',
            'Oil or chemical spill to marine or terrestrial environment',
            'Flaring or venting above permitted limits',
            'Wildlife habitat destruction',
            'Fire from hydrocarbon release',
            'Dropped object in lifting operation',
            'Explosion in compressor building',
            'Caught in machinery or rotating equipment',
            'Lifting or manual handling injury',
            'Electric shock or arc flash',
            'Excavation cave-in',
            'Workers drowned',
            'Other',
        ];
    }

    // --- METHOD BARU UNTUK PRINT PDF ---
    public function printPdf()
    {
        // 1. Ambil semua data register
        // (Saya asumsikan model Anda punya semua kolom yang Anda sebutkan)
        $registers = MahRegister::all();

        // 2. Data yang akan dikirim ke view PDF
        $data = [
            'title' => 'REGISTER MAH INTEGRATED TERMINAL BANJARMASIN',
            'date' => date('d/m/Y H:i:s'),
            'registers' => $registers
        ];

        // 3. Muat view Blade-nya
        $pdf = Pdf::loadView('mah.report-pdf', $data);

        // 4. Atur orientasi kertas (karena kolomnya banyak, kita WAJIB pakai landscape)
        $pdf->setPaper('a4', 'landscape');

        // 5. Download PDF-nya dengan nama file kustom
        return $pdf->download('laporan-lengkap-mah-register-' . date('Ymd') . '.pdf');
    }
}