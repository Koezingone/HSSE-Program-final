<?php

namespace App\Http\Controllers;

use App\Models\MahRegister;
use App\Models\RiskControl;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB; // <-- Pastikan ini ada
use Illuminate\Support\Collection; // <-- Tambahkan ini

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

        // 2. DATA UNTUK CHART "STATUS ACTION PLAN" (BAR CHART) - Individual
        $action_status_data = RiskControl::query()
            ->select('action_status', DB::raw('COUNT(*) as total'))
            ->whereNotNull('action_status')->where('action_status', '!=', '')
            ->groupBy('action_status')
            ->pluck('total', 'action_status')->all();

        // 3. DATA UNTUK CHART "SEBARAN FINAL RISK" (PIE CHART) - Dari MAH Register Overall
        $finalRiskSqlOverall = $this->getRiskCategorySql('final_risk', true);
        $final_risk_data = MahRegister::query()
            ->select(DB::raw("$finalRiskSqlOverall as risk_category"), DB::raw('COUNT(*) as total'))
            ->groupBy('risk_category')
            ->pluck('total', 'risk_category')->all();

        // 4. DATA UNTUK CHART "MAH HAZARD CATEGORY" (PIE CHART)
        $hazard_category_data = MahRegister::query()
            ->select('hazard_category', DB::raw('COUNT(*) as total'))
            ->whereNotNull('hazard_category')->where('hazard_category', '!=', '')
            ->groupBy('hazard_category')
            ->pluck('total', 'hazard_category')->all();

        // 5. DATA UNTUK BANYAK BAR CHART (Residual & Final Risk per Hazard Cat.)
        $residualRiskSql = $this->getRiskCategorySql('residual_risk', false);
        $finalRiskSql = $this->getRiskCategorySql('final_risk', true);
        $residualData = MahRegister::query()->select('hazard_category', DB::raw("$residualRiskSql as risk_category"), DB::raw('COUNT(*) as total'))->whereNotNull('hazard_category')->where('hazard_category', '!=', '')->where('residual_risk', '>', 0)->groupBy('hazard_category', 'risk_category')->get()->groupBy('hazard_category');
        $finalData = MahRegister::query()->select('hazard_category', DB::raw("$finalRiskSql as risk_category"), DB::raw('COUNT(*) as total'))->whereNotNull('hazard_category')->where('hazard_category', '!=', '')->groupBy('hazard_category', 'risk_category')->get()->groupBy('hazard_category');
        $finalBarChartsData = [];
        $riskCategoryLabels = ['On Progress', 'Low (1-3)', 'Low to Moderate (4)', 'Medium (5-9)', 'Moderate to High (10-12)', 'High (15-25)', 'Other'];
        $riskCategoriesTemplate = array_fill_keys($riskCategoryLabels, 0);
        $colors = ['On Progress' => '#6c757d', 'Low (1-3)' => '#00a65a', 'Low to Moderate (4)' => '#00c0ef', 'Medium (5-9)' => '#ffc107', 'Moderate to High (10-12)' => '#f39c12', 'High (15-25)' => '#f56954', 'Other' => '#d2d6de'];
        $backgroundColors = array_map(function ($label) use ($colors) {
            return $colors[$label] ?? '#d2d6de';
        }, $riskCategoryLabels);
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
            $residualValues = array_values($residualCounts);
            $finalCounts = $riskCategoriesTemplate;
            if (isset($finalData[$hazardCategory])) {
                foreach ($finalData[$hazardCategory] as $item) {
                    if (isset($finalCounts[$item->risk_category])) {
                        $finalCounts[$item->risk_category] = $item->total;
                    }
                }
            }
            $finalValues = array_values($finalCounts);
            if (array_sum($residualValues) > 0 || array_sum($finalValues) > 0) {
                $finalBarChartsData[$hazardCategory] = ['labels' => $riskCategoryLabels, 'colors' => $backgroundColors, 'residualData' => $residualValues, 'finalData' => $finalValues];
            }
        }

        // 6. (DIMASUKKAN KEMBALI) DATA UNTUK 4 PIE CHART (Overall Status per Studi)
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

            if (array_sum($chartValues) > 0) {
                $studyStatusChartsData[$study] = ['labels' => $statusLabels, 'values' => $chartValues, 'colors' => $statusColors];
            } else {
                $studyStatusChartsData[$study] = null;
            }
        }

        // (BARU) DATA UNTUK PETA LOKASI
        $locationCounts = RiskControl::query()
            ->select('location', DB::raw('COUNT(DISTINCT mah_register_id) as mah_count'))
            ->whereNotNull('location')
            ->where('location', '!=', '')
            ->groupBy('location')
            ->pluck('mah_count', 'location')
            ->all();

        // (BARU) Definisikan koordinat manual (Y, X) untuk setiap lokasi di gambar Anda
        // **!!! ANDA HARUS MENYESUAIKAN KOORDINAT INI !!!**
        $locationCoordinates = [
            'Area Tank Yard' => [150, 300], // Contoh: Y=150, X=300 piksel dari kiri-atas
            'Area Jetty 1' => [50, 600],
            'Area Jetty 2' => [100, 650],
            'Area Jetty 3' => [150, 700],
            'Area Fillingshed' => [300, 400],
            'Area Dermaga' => [80, 500],
            'Kantor' => [400, 100],
            'Parkir Mobil Tangki' => [350, 500],
            'Area IT Banjarmasin' => [450, 150], // Sesuaikan!
            'Lab QQ' => [400, 200], // Sesuaikan!
            'Gudang Limbah B3' => [300, 100], // Sesuaikan!
            'Rumah Pompa Produk' => [250, 250], // Sesuaikan!
            'Rumah Pompa PMK' => [280, 200], // Sesuaikan!
            'Workshop' => [380, 250], // Sesuaikan!
            'Oil Catcher' => [200, 500], // Sesuaikan!
            'Control Room Depan' => [420, 50], // Sesuaikan!
            'Control Room Belakang' => [200, 100], // Sesuaikan!
            'Ruang Genset' => [350, 150], // Sesuaikan!
            // Tambahkan lokasi lain jika ada...
        ];

        // Gabungkan jumlah dengan koordinat
        $mapMarkersData = [];
        foreach ($locationCounts as $location => $count) {
            if (isset($locationCoordinates[$location])) {
                $mapMarkersData[] = [
                    'name' => $location,
                    'coords' => $locationCoordinates[$location],
                    'count' => $count
                ];
            }
        }

        // 7. KIRIM SEMUA DATA KE VIEW
        return view('dashboard', [
            // Info Box
            'total_mah' => $total_mah,
            'total_actions' => $total_actions,
            'overall_open' => $overall_open,
            'overall_on_progress' => $overall_on_progress,
            'overall_close' => $overall_close,
            // Chart Status Individual
            'action_status_labels' => json_encode(array_keys($action_status_data)),
            'action_status_values' => json_encode(array_values($action_status_data)),
            // Chart Final Risk (Overall Pie)
            'final_risk_labels' => json_encode(array_keys($final_risk_data)),
            'final_risk_values' => json_encode(array_values($final_risk_data)),
            // Chart Hazard Category
            'hazard_category_labels' => json_encode(array_keys($hazard_category_data)),
            'hazard_category_values' => json_encode(array_values($hazard_category_data)),
            // Banyak Bar Chart (Residual vs Final)
            'riskBarChartsData' => json_encode($finalBarChartsData),
            // (DIMASUKKAN KEMBALI) Data untuk 4 Pie Chart Status per Studi
            'studyStatusChartsData' => json_encode($studyStatusChartsData),

            // (BARU) Kirim data marker peta
            'mapMarkersData' => json_encode($mapMarkersData),
        ]);
    }

    // Helper SQL CASE (Tidak berubah)
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
     * (BARU) Menampilkan halaman dashboard Risk Profile per Category.
     */
    public function riskProfileDashboard()
    {
        // --- Logika untuk mengambil & memproses data BAR CHART saja ---
        // (Kita salin dari method index() sebelumnya)
        $residualRiskSql = $this->getRiskCategorySql('residual_risk', false);
        $finalRiskSql = $this->getRiskCategorySql('final_risk', true);
        $residualData = MahRegister::query()->select('hazard_category', DB::raw("$residualRiskSql as risk_category"), DB::raw('COUNT(*) as total'))->whereNotNull('hazard_category')->where('hazard_category', '!=', '')->where('residual_risk', '>', 0)->groupBy('hazard_category', 'risk_category')->get()->groupBy('hazard_category');
        $finalData = MahRegister::query()->select('hazard_category', DB::raw("$finalRiskSql as risk_category"), DB::raw('COUNT(*) as total'))->whereNotNull('hazard_category')->where('hazard_category', '!=', '')->groupBy('hazard_category', 'risk_category')->get()->groupBy('hazard_category');
        $finalBarChartsData = [];
        $riskCategoryLabels = ['On Progress', 'Low (1-3)', 'Low to Moderate (4)', 'Medium (5-9)', 'Moderate to High (10-12)', 'High (15-25)', 'Other'];
        $riskCategoriesTemplate = array_fill_keys($riskCategoryLabels, 0);
        $colors = ['On Progress' => '#6c757d', 'Low (1-3)' => '#00a65a', 'Low to Moderate (4)' => '#00c0ef', 'Medium (5-9)' => '#ffc107', 'Moderate to High (10-12)' => '#f39c12', 'High (15-25)' => '#f56954', 'Other' => '#d2d6de'];
        $backgroundColors = array_map(function ($label) use ($colors) {
            return $colors[$label] ?? '#d2d6de';
        }, $riskCategoryLabels);
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
            $residualValues = array_values($residualCounts);
            $finalCounts = $riskCategoriesTemplate;
            if (isset($finalData[$hazardCategory])) {
                foreach ($finalData[$hazardCategory] as $item) {
                    if (isset($finalCounts[$item->risk_category])) {
                        $finalCounts[$item->risk_category] = $item->total;
                    }
                }
            }
            $finalValues = array_values($finalCounts);
            if (array_sum($residualValues) > 0 || array_sum($finalValues) > 0) {
                $finalBarChartsData[$hazardCategory] = ['labels' => $riskCategoryLabels, 'colors' => $backgroundColors, 'residualData' => $residualValues, 'finalData' => $finalValues];
            }
        }
        // --- Akhir logika data Bar Chart ---

        // Kirim HANYA data yang dibutuhkan ke view baru
        return view('dashboard_risk_profile', [ // <-- Nama view baru
            'riskBarChartsData' => json_encode($finalBarChartsData),
        ]);
    }
}
