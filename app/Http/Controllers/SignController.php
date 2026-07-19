<?php

namespace App\Http\Controllers;

use App\Models\Datasets;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\File;

class SignController extends Controller
{
    private string $flaskUrl;

    // Kategori model yang dilatih terpisah di Colab. Kalau nanti nambah
    // kategori baru (misal "kata"), tinggal tambah di sini + di skrip Colab.
    private const KATEGORI_LIST = ['huruf', 'angka'];

    public function __construct()
    {
        $this->flaskUrl = 'https://signnet-ai-skripsi-anda.loca.lt';
    }

    /**
     * Path file metadata JSON untuk 1 kategori.
     */
    private function metaPath(string $kategori): string
    {
        return storage_path("app/ai_metadata/meta_model_{$kategori}.json");
    }

    /**
     * Baca metadata 1 kategori. Null kalau belum ada / gagal parse.
     */
    private function readMeta(string $kategori): ?array
    {
        $path = $this->metaPath($kategori);
        if (!File::exists($path)) {
            return null;
        }
        try {
            $data = json_decode(File::get($path), true);
            return is_array($data) ? $data : null;
        } catch (\Exception $e) {
            Log::error("Gagal parsing meta_model_{$kategori}.json: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Endpoint yang dipanggil Colab untuk mengirim file hasil training.
     * Sekarang WAJIB menyertakan field 'kategori' (huruf/angka) supaya
     * file tidak saling menimpa antar kategori.
     */
    public function updateModelFiles(Request $request)
    {
        set_time_limit(0);
        ini_set('memory_limit', '512M');

        try {
            $request->validate([
                'onnx_model' => 'required|file',
                'meta_model' => 'required|file',
                'labels'     => 'required|file',
                'kategori'   => 'required|string|in:' . implode(',', self::KATEGORI_LIST),
            ]);

            $kategori = $request->input('kategori');

            $destinationPath = public_path('models');
            if (!File::exists($destinationPath)) {
                File::makeDirectory($destinationPath, 0755, true);
            }

            $storageMetadataDirectory = storage_path('app/ai_metadata');
            if (!File::exists($storageMetadataDirectory)) {
                File::makeDirectory($storageMetadataDirectory, 0755, true);
            }

            $request->file('onnx_model')->move($destinationPath, "rf_model_{$kategori}.onnx.gz");
            $request->file('labels')->move($destinationPath, "labels_{$kategori}.json");
            $request->file('meta_model')->move($storageMetadataDirectory, "meta_model_{$kategori}.json");

            // Dipakai frontend/mobile untuk cek apakah ada versi model baru
            // yang perlu di-fetch ulang (per kategori, supaya update huruf
            // tidak memaksa reload model angka juga, begitu sebaliknya).
            file_put_contents(public_path("models/model_version_{$kategori}.txt"), time());

            return response()->json([
                'status'  => 'success',
                'message' => "Update model kategori '{$kategori}' berhasil",
            ], 200);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Validasi gagal: ' . implode(', ', $e->validator->errors()->all()),
            ], 422);
        } catch (\Exception $e) {
            Log::error('updateModelFiles error: ' . $e->getMessage());
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * Dipoll oleh halaman training admin setelah tombol "Train Model"
     * ditekan. Mengembalikan status gabungan huruf + angka.
     *
     * Bentuk response baru:
     * {
     *   "status": "ready" | "partial" | "pending",
     *   "kategori": {
     *      "huruf": { accuracy, classification_report, ... } | null,
     *      "angka": { accuracy, classification_report, ... } | null
     *   }
     * }
     *
     * "ready"   -> kedua kategori sudah selesai
     * "partial" -> baru salah satu kategori yang selesai (kategori lain
     *              mungkin di-skip karena datanya kurang dari 30 baris,
     *              atau masih diproses)
     * "pending" -> belum ada satupun yang selesai
     */
    public function getLatestEvaluation()
    {
        try {
            $hasil = [];
            $jumlahSiap = 0;

            foreach (self::KATEGORI_LIST as $kategori) {
                $data = $this->readMeta($kategori);
                $hasil[$kategori] = $data;
                if ($data !== null) {
                    $jumlahSiap++;
                }
            }

            if ($jumlahSiap === 0) {
                return response()->json([
                    'status'   => 'pending',
                    'message'  => 'Model masih dalam proses pelatihan di Google Colab...',
                    'kategori' => $hasil,
                ], 200);
            }

            $status = $jumlahSiap === count(self::KATEGORI_LIST) ? 'ready' : 'partial';

            return response()->json([
                'status'   => $status,
                'kategori' => $hasil,
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Gagal membaca file evaluasi: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function trainModel()
    {
        try {
            $totalData = Datasets::count();
            if ($totalData < 5) {
                return response()->json([
                    'status'  => 'error',
                    'message' => "Dataset terlalu sedikit ({$totalData} sampel). Minimal 5 sampel diperlukan.",
                ], 400);
            }

            // Hapus metadata LAMA kedua kategori supaya polling tidak salah
            // membaca hasil training sebelumnya sebagai hasil yang baru.
            foreach (self::KATEGORI_LIST as $kategori) {
                $path = $this->metaPath($kategori);
                if (File::exists($path)) {
                    File::delete($path);
                }
            }

            try {
                Http::timeout(2)->post("{$this->flaskUrl}/train-cloud");
            } catch (\Illuminate\Http\Client\ConnectionException $e) {
                // Sengaja diabaikan: request ke Colab memang async / long-running,
                // timeout 2 detik di sini hanya untuk melepas trigger saja.
            }

            return response()->json([
                'status'  => 'success',
                'message' => 'Proses training (huruf & angka) berhasil dipicu di Cloud Server! Hasilnya akan dikirim otomatis ke server setelah masing-masing kategori selesai.',
                'async'   => true,
            ], 200);

        } catch (\Exception $e) {
            Log::error('trainModel error: ' . $e->getMessage());
            return response()->json([
                'status'  => 'error',
                'message' => 'Terjadi kesalahan sistem: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function getModelStats()
    {
        try {
            $totalData   = Datasets::count();
            $totalLabels = Datasets::distinct('label')->count('label');
            $labelStats = Datasets::selectRaw('label, COUNT(*) as jumlah')
                ->groupBy('label')
                ->orderBy('label')
                ->pluck('jumlah', 'label');

            return response()->json([
                'status'       => 'success',
                'total'        => $totalData,
                'total_data'   => $totalData,
                'total_labels' => $totalLabels,
                'stats'        => $labelStats,
            ], 200);

        } catch (\Exception $e) {
            Log::error('getModelStats error: ' . $e->getMessage());
            return response()->json([
                'status'  => 'error',
                'message' => 'Gagal mengambil statistik: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function collect(Request $request)
    {
        try {
            $request->validate([
                'label'    => 'required|string|max:50',
                'features' => 'required|array|size:126',
            ]);

            $adminId = 1;
            if (Auth::check()) {
                $user    = Auth::user();
                $adminId = $user?->id_admin ?? $user?->id ?? 1;
            }

            $featuresData = $request->input('features');
            $featuresJson = is_array($featuresData) ? json_encode($featuresData) : $featuresData;

            Datasets::create([
                'admin_id' => $adminId,
                'features' => $featuresJson,
                'label'    => strtoupper(trim($request->input('label'))),
            ]);

            return response()->json([
                'status'  => 'success',
                'message' => 'Data koordinat berhasil disimpan.',
            ], 200);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Validasi gagal: ' . implode(', ', $e->validator->errors()->all()),
            ], 422);
        } catch (\Exception $e) {
            Log::error('collect error: ' . $e->getMessage());
            return response()->json([
                'status'  => 'error',
                'message' => 'Gagal menyimpan data: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Dashboard utama. classification_report digabung dari kedua kategori
     * (labelnya tidak akan bentrok karena huruf A-Z vs digit 0-9), dan
     * akurasi ditampilkan terpisah per kategori supaya tidak menyesatkan
     * (akurasi huruf & angka TIDAK bisa dirata-ratakan begitu saja karena
     * jumlah sampel & kompleksitas kelasnya berbeda).
     */
    public function getDashboardStats()
    {
        try {
            $totalData   = Datasets::count();
            $totalLabels = Datasets::distinct('label')->count('label');
            $labelStats  = Datasets::selectRaw('label, COUNT(*) as jumlah')
                ->groupBy('label')
                ->orderBy('label')
                ->pluck('jumlah', 'label');

            $classificationReport = [];
            foreach ($labelStats as $label => $jumlah) {
                $cleanLabel = strtoupper(trim((string) $label));
                $classificationReport[$cleanLabel] = [
                    'precision' => 0,
                    'recall'    => 0,
                    'f1-score'  => 0,
                    'support'   => $jumlah,
                ];
            }

            $accuracyPerKategori = [];
            $confusionMatrixPerKategori = [];
            $statusFitPerKategori = [];

            foreach (self::KATEGORI_LIST as $kategori) {
                $data = $this->readMeta($kategori);

                if ($data === null) {
                    $accuracyPerKategori[$kategori] = null;
                    $confusionMatrixPerKategori[$kategori] = [];
                    $statusFitPerKategori[$kategori] = null;
                    continue;
                }

                $accuracyPerKategori[$kategori] = isset($data['accuracy']) ? (float) $data['accuracy'] : 0.0;
                $confusionMatrixPerKategori[$kategori] = $data['confusion_matrix'] ?? [];
                $statusFitPerKategori[$kategori] = $data['model_fitting_status'] ?? null;

                if (!empty($data['classification_report'])) {
                    foreach ($data['classification_report'] as $labelKey => $metrics) {
                        $stringLabel = strtoupper(trim((string) $labelKey));
                        if (!is_array($metrics)) {
                            continue;
                        }
                        $supportCount = $labelStats[$stringLabel] ?? ($metrics['support'] ?? 0);
                        $classificationReport[$stringLabel] = [
                            'precision' => $metrics['precision'] ?? 0,
                            'recall'    => $metrics['recall'] ?? 0,
                            'f1-score'  => $metrics['f1-score'] ?? 0,
                            'support'   => $supportCount,
                        ];
                    }
                }
            }

            return response()->json([
                'status'                    => 'success',
                'total'                     => $totalData,
                'total_data'                => $totalData,
                'total_labels'              => $totalLabels,
                'stats'                     => $labelStats,
                'accuracy_per_kategori'     => $accuracyPerKategori,
                'model_fitting_per_kategori' => $statusFitPerKategori,
                'confusion_matrix_per_kategori' => $confusionMatrixPerKategori,
                'classification_report'    => $classificationReport,
            ], 200);

        } catch (\Exception $e) {
            Log::error('getDashboardStats error utama: ' . $e->getMessage());
            return response()->json([
                'status'  => 'error',
                'message' => 'Gagal memuat data statistik dashboard: ' . $e->getMessage(),
            ], 500);
        }
    }
}