<?php

namespace App\Http\Controllers;

use App\Models\MahRegister;
use App\Models\RiskControl;
use App\Models\BarrierAssessment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;


class DashboardController extends Controller
{
    /**
     * Menampilkan halaman dashboard utama.
     */
    public function index()
    {
        // 1. KOTAK INFO (INFO BOXES)
        $total_mah = RiskControl::distinct('mah_register_id')->count();
        $total_actions = RiskControl::count();
        $overall_open = MahRegister::whereHas('riskControls')->where('overall_status', 'OPEN')->count();
        $overall_on_progress = MahRegister::whereHas('riskControls')->where('overall_status', 'ON PROGRESS')->count();
        $overall_close = MahRegister::whereHas('riskControls')->where('overall_status', 'CLOSE')->count();

        // 2. DATA UNTUK CHART "STATUS ACTION PLAN"
        $action_status_data = RiskControl::query()
            ->select('action_status', DB::raw('COUNT(*) as total'))
            ->whereNotNull('action_status')
            ->where('action_status', '!=', '')
            ->groupBy('action_status')
            ->pluck('total', 'action_status')
            ->all();

        // 3. DATA UNTUK CHART "FINAL RISK (OVERALL)"
        $finalRiskSqlOverall = $this->getRiskCategorySql('final_risk', true);
        $final_risk_data = MahRegister::query()
            ->select(DB::raw("$finalRiskSqlOverall as risk_category"), DB::raw('COUNT(*) as total'))
            ->groupBy('risk_category')
            ->pluck('total', 'risk_category')
            ->all();

        // 4. DATA UNTUK CHART "HAZARD CATEGORY"
        $hazard_category_data = MahRegister::query()
            ->select('hazard_category', DB::raw('COUNT(*) as total'))
            ->whereNotNull('hazard_category')
            ->where('hazard_category', '!=', '')
            ->groupBy('hazard_category')
            ->pluck('total', 'hazard_category')
            ->all();

        // 5. DATA UNTUK BAR CHART (Residual vs Final Risk per Hazard Category)
        $residualRiskSql = $this->getRiskCategorySql('residual_risk', false);
        $finalRiskSql = $this->getRiskCategorySql('final_risk', true);

        $residualData = MahRegister::query()
            ->select('hazard_category', DB::raw("$residualRiskSql as risk_category"), DB::raw('COUNT(*) as total'))
            ->whereNotNull('hazard_category')
            ->where('hazard_category', '!=', '')
            ->where('residual_risk', '>', 0)
            ->groupBy('hazard_category', 'risk_category')
            ->get()
            ->groupBy('hazard_category');

        $finalData = MahRegister::query()
            ->select('hazard_category', DB::raw("$finalRiskSql as risk_category"), DB::raw('COUNT(*) as total'))
            ->whereNotNull('hazard_category')
            ->where('hazard_category', '!=', '')
            ->groupBy('hazard_category', 'risk_category')
            ->get()
            ->groupBy('hazard_category');

        $finalBarChartsData = [];
        $riskCategoryLabels = [
            'On Progress',
            'Low (1-3)',
            'Low to Moderate (4)',
            'Medium (5-9)',
            'Moderate to High (10-12)',
            'High (15-25)',
            'Other'
        ];
        $riskCategoriesTemplate = array_fill_keys($riskCategoryLabels, 0);
        $colors = [
            'On Progress' => '#6c757d',
            'Low (1-3)' => '#00a65a',
            'Low to Moderate (4)' => '#00c0ef',
            'Medium (5-9)' => '#ffc107',
            'Moderate to High (10-12)' => '#f39c12',
            'High (15-25)' => '#f56954',
            'Other' => '#d2d6de'
        ];
        $backgroundColors = array_map(fn($label) => $colors[$label] ?? '#d2d6de', $riskCategoryLabels);

        $allHazardCategories = $residualData->keys()->merge($finalData->keys())->unique()->sort();

        foreach ($allHazardCategories as $hazardCategory) {
            $residualCounts = $riskCategoriesTemplate;
            if (isset($residualData[$hazardCategory])) {
                foreach ($residualData[$hazardCategory] as $item) {
                    if (isset($residualCounts[$item->risk_category])) {
                        $residualCounts[$item->risk_category] = $item->total;
                    }
                }
            }

            $finalCounts = $riskCategoriesTemplate;
            if (isset($finalData[$hazardCategory])) {
                foreach ($finalData[$hazardCategory] as $item) {
                    if (isset($finalCounts[$item->risk_category])) {
                        $finalCounts[$item->risk_category] = $item->total;
                    }
                }
            }

            $residualValues = array_values($residualCounts);
            $finalValues = array_values($finalCounts);

            if (array_sum($residualValues) > 0 || array_sum($finalValues) > 0) {
                $finalBarChartsData[$hazardCategory] = [
                    'labels' => $riskCategoryLabels,
                    'colors' => $backgroundColors,
                    'residualData' => $residualValues,
                    'finalData' => $finalValues,
                ];
            }
        }

        // 6. DATA UNTUK 4 PIE CHART (Overall Status per Studi)
        $studyTypes = ['HAZOP', 'HAZID', 'MAH IDENTIFICATION', 'FERA'];
        $studyStatusChartsData = [];
        $statusLabels = ['OPEN', 'ON PROGRESS', 'CLOSE'];
        $statusColors = ['#dc3545', '#ffc107', '#28a745'];

        foreach ($studyTypes as $study) {
            $data = MahRegister::query()
                ->whereHas('riskControls', function ($query) use ($study) {
                    $query->where('referensi_sudi', 'LIKE', '%' . $study . '%');
                })
                ->select('overall_status', DB::raw('COUNT(*) as total'))
                ->whereIn('overall_status', $statusLabels)
                ->groupBy('overall_status')
                ->pluck('total', 'overall_status')
                ->all();

            $chartValues = [$data['OPEN'] ?? 0, $data['ON PROGRESS'] ?? 0, $data['CLOSE'] ?? 0];
            $studyStatusChartsData[$study] = array_sum($chartValues) > 0
                ? ['labels' => $statusLabels, 'values' => $chartValues, 'colors' => $statusColors]
                : null;
        }

        // 7. DATA UNTUK PETA (MAP)
        $locationCounts = RiskControl::query()
            ->select('location', DB::raw('COUNT(DISTINCT mah_register_id) as mah_count'))
            ->whereNotNull('location')
            ->where('location', '!=', '')
            ->groupBy('location')
            ->pluck('mah_count', 'location')
            ->all();

        $locationCoordinates = [
            'Area Tank Yard' => [300, 450],
            'Area Jetty 1' => [550, 1050],
            'Area Jetty 2' => [550, 800],
            'Area Jetty 3' => [590, 490],
            'Area Fillingshed' => [100, 600],
            'Area Dermaga' => [550, 650],
            'Kantor' => [80, 420],
            'Parkir Mobil Tangki' => [120, 150],
            'Area IT Banjarmasin' => [600, 50],
            'Lab QQ' => [50, 500],
            'Gudang Limbah B3' => [270, 130],
            'Rumah Pompa Produk' => [160, 740],
            'Rumah Pompa PMK' => [560, 500],
            'Workshop' => [380, 1150],
            'Oil Catcher' => [420, 950],
            'Control Room Depan' => [105, 410],
            'Control Room Belakang' => [460, 460],
            'Ruang Genset' => [450, 650],
        ];

        $mapMarkersData = [];
        foreach ($locationCounts as $location => $count) {
            if (isset($locationCoordinates[$location])) {
                $mapMarkersData[] = [
                    'name' => $location,
                    'coords' => $locationCoordinates[$location],
                    'count' => $count,
                ];
            }
        }

        // 8. DATA UNTUK BOWTIE LAYOUT (BARRIER PERFORMANCE)
        $barrierAssessments = BarrierAssessment::query()
            ->select('barrier_category', 'specific_barrier', 'barrier_type', 'percentage')
            ->orderBy('assessment_date', 'desc')
            ->get();

        // Inisialisasi DUA array terpisah
        $baseCategories = [
            'Prevention' => [],
            'Detect and Control' => [],
            'Mitigation' => [],
            'Emergency Response' => [],
        ];
        $groupedBarriersHardware = $baseCategories;
        $groupedBarriersHuman = $baseCategories;

        $barrierStructure = $this->getBarrierOptions();

        foreach ($barrierAssessments as $assessment) {
            $assessmentName = trim(strtolower($assessment->specific_barrier));
            $categoryFound = null;

            // Loop untuk mencocokkan kategori di struktur Bowtie
            foreach ($barrierStructure as $catName => $barriers) {
                foreach ($barriers as $b) {
                    if (trim(strtolower($b['name'])) === $assessmentName) {
                        $categoryFound = $catName;
                        break 2;
                    }
                }
            }

            // Jika tidak ditemukan kategori yang cocok, log peringatannya
            if (!$categoryFound) {
                Log::warning('Barrier not matched:', ['name' => $assessment->specific_barrier]);
                continue; // Lanjut ke assessment berikutnya
            }

            $barrierData = [
                'name' => $assessment->specific_barrier,
                'percentage' => $assessment->percentage, // <-- Hapus '?? 0'
                'type' => $assessment->barrier_type ?? 'Unknown',
            ];

            // Pisahkan data berdasarkan barrier_type
            if ($assessment->barrier_type === 'Hardware' && isset($groupedBarriersHardware[$categoryFound])) {
                $groupedBarriersHardware[$categoryFound][] = $barrierData;
            } elseif ($assessment->barrier_type === 'Human' && isset($groupedBarriersHuman[$categoryFound])) {
                $groupedBarriersHuman[$categoryFound][] = $barrierData;
            }
            // Data dengan tipe 'Unknown' atau null akan diabaikan dari kedua chart
        }


        // 8b. DATA UNTUK CHART BARRIER PERFORMANCE (Hardware vs Human)
        $barrierData = BarrierAssessment::select('barrier_category', 'barrier_type', DB::raw('AVG(percentage) as avg_percentage'))
            ->whereNotNull('barrier_type')
            ->groupBy('barrier_category', 'barrier_type')
            ->get();

        // Kategori unik untuk sumbu X
        $barrierPerfLabels = $barrierData->pluck('barrier_category')->unique()->values();

        // Rata-rata per tipe
        $barrierPerfHardwareValues = $barrierData->where('barrier_type', 'Hardware')->pluck('avg_percentage')->values();
        $barrierPerfHumanValues = $barrierData->where('barrier_type', 'Human')->pluck('avg_percentage')->values();


        // 8c. DATA UNTUK TABEL PROGRESS ACTION PLAN (OPEN)
        $openActionPlans = \App\Models\RiskControl::with('mahRegister') // Muat relasi mahRegister
            ->where('action_status', 'OPEN')
            ->orderBy('persentase', 'asc') // Urutkan dari progress terkecil
            ->get(['mah_register_id', 'action_plan', 'persentase']);

        // 9. KIRIM SEMUA DATA KE VIEW
        return view('dashboard', [
            'total_mah' => $total_mah,
            'total_actions' => $total_actions,
            'overall_open' => $overall_open,
            'overall_on_progress' => $overall_on_progress,
            'overall_close' => $overall_close,
            'action_status_labels' => json_encode(array_keys($action_status_data)),
            'action_status_values' => json_encode(array_values($action_status_data)),
            'final_risk_labels' => json_encode(array_keys($final_risk_data)),
            'final_risk_values' => json_encode(array_values($final_risk_data)),
            'hazard_category_labels' => json_encode(array_keys($hazard_category_data)),
            'hazard_category_values' => json_encode(array_values($hazard_category_data)),
            'riskBarChartsData' => json_encode($finalBarChartsData),
            'studyStatusChartsData' => json_encode($studyStatusChartsData),
            'mapMarkersData' => json_encode($mapMarkersData),
            'groupedBarriersHardware' => $groupedBarriersHardware,
            'groupedBarriersHuman' => $groupedBarriersHuman,

            // Tambahkan variabel ini:
            'barrierPerfLabels' => json_encode($barrierPerfLabels),
            'barrierPerfHardwareValues' => json_encode($barrierPerfHardwareValues),
            'barrierPerfHumanValues' => json_encode($barrierPerfHumanValues),

            'openActionPlans' => $openActionPlans,
        ]);
    }

    /**
     * Helper SQL CASE untuk kategori risiko
     */
    private function getRiskCategorySql($columnName, $includeOnProgress = false)
    {
        $sql = "CASE
            WHEN $columnName BETWEEN 15 AND 25 THEN 'High (15-25)'
            WHEN $columnName BETWEEN 10 AND 12 THEN 'Moderate to High (10-12)'
            WHEN $columnName BETWEEN 5 AND 9 THEN 'Medium (5-9)'
            WHEN $columnName = 4 THEN 'Low to Moderate (4)'
            WHEN $columnName BETWEEN 1 AND 3 THEN 'Low (1-3)'";
        if ($includeOnProgress) {
            $sql .= " WHEN $columnName IS NULL OR $columnName <= 0 THEN 'On Progress'";
        }
        $sql .= " ELSE 'Other' END";
        return $sql;
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
