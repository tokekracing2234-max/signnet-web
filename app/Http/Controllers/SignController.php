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
    private string $metaPath;

    public function __construct()
    {
        // 1. Mengarah langsung ke URL publik terowongan Localtunnel Google Colab kamu
        $this->flaskUrl = 'https://signnet-ai-skripsi-anda.loca.lt';
        $this->metaPath = storage_path('app/ai_metadata/meta_model.json');
    }

    // =========================================================================
    // BARU & MODIFIKASI: Menerima file biner dan JSON kiriman dari Flask secara internal
    // =========================================================================
    public function updateModelFiles(Request $request)
    {
        // Beri waktu dan memori ekstra untuk memproses file besar
        set_time_limit(0);
        ini_set('memory_limit', '512M');

        try {
            $request->validate([
                'onnx_model' => 'required|file',
                'meta_model' => 'required|file',
                'labels'     => 'required|file',
            ]);

            $destinationPath = public_path('models');
            if (!File::exists($destinationPath)) {
                File::makeDirectory($destinationPath, 0755, true);
            }

            // Gunakan storeAs atau move dengan pengecekan
            $request->file('onnx_model')->move($destinationPath, 'rf_model.onnx');
            $request->file('labels')->move($destinationPath, 'labels.json');

            $storageMetadataDirectory = storage_path('app/ai_metadata');
            if (!File::exists($storageMetadataDirectory)) {
                File::makeDirectory($storageMetadataDirectory, 0755, true);
            }
            
            // Menyimpan file meta_model.json terbaru (Berfungsi sebagai flag pemicu UI)
            $request->file('meta_model')->move($storageMetadataDirectory, 'meta_model.json');

            return response()->json(['status' => 'success', 'message' => 'Update berhasil'], 200);

        } catch (\Exception $e) {
            Log::error('updateModelFiles error: ' . $e->getMessage());
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 500);
        }
    }

    // =========================================================================
    // TUGAS BARU: Endpoint khusus yang dicari oleh fungsi polling JavaScript Anda
    // =========================================================================
    public function getLatestEvaluation()
    {
        try {
            // Cek fisik: Jika file meta_model.json ada, berarti Flask telah sukses mengirimkan file hasil training terbaru
            if (File::exists($this->metaPath)) {
                $jsonContent = File::get($this->metaPath);
                $data = json_decode($jsonContent, true);

                return response()->json([
                    'status' => 'ready',
                    'accuracy' => $data['accuracy'] ?? 0.0,
                    'classification_report' => $data['classification_report'] ?? []
                ], 200);
            }

            // Jika file belum ada/di-delete saat start, berarti model masih dalam proses pelatihan di Google Colab
            return response()->json([
                'status' => 'pending',
                'message' => 'Model masih dalam proses pelatihan di Google Colab...'
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Gagal membaca file evaluasi: ' . $e->getMessage()
            ], 500);
        }
    }

    // =========================================================================
    // PERBAIKAN: Diubah menjadi pemicu Asinkronus agar terhindar dari timeout 503 Railway
    // =========================================================================
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

            // HAPUS file metadata lama sebelum training dimulai
            // Ini krusial agar polling JS mendeteksi status 'pending' (404/file tidak ditemukan)
            if (File::exists($this->metaPath)) {
                File::delete($this->metaPath);
            }

            // Tembak Google Colab dengan timeout sangat tipis (2 detik). 
            try {
                Http::timeout(2)->post("{$this->flaskUrl}/train-cloud");
            } catch (\Illuminate\Http\Client\ConnectionException $e) {
                // Abaikan timeout koneksi pendek karena sinyal pemicu sudah masuk ke Flask background thread
            }

            return response()->json([
                'status'  => 'success',
                'message' => 'Proses training berhasil dipicu di Cloud Server! Hasilnya akan dikirim otomatis ke server setelah selesai.',
                'async'   => true
            ], 200);

        } catch (\Exception $e) {
            Log::error('trainModel error: ' . $e->getMessage());
            return response()->json([
                'status'  => 'error',
                'message' => 'Terjadi kesalahan sistem: ' . $e->getMessage(),
            ], 500);
        }
    }

    // =========================================================================
    // JANGAN DIUBAH: Khusus melayani loadInitialData() di trainmodel.blade.php
    // =========================================================================
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

    // =========================================================================
    // Fungsi khusus yang digabungkan untuk halaman dashboard.blade.php
    // =========================================================================
    public function getDashboardStats()
    {
        try {
            // 1. Ambil data agregasi dasar dari Database
            $totalData   = Datasets::count();
            $totalLabels = Datasets::distinct('label')->count('label');
            $labelStats  = Datasets::selectRaw('label, COUNT(*) as jumlah')
                ->groupBy('label')
                ->orderBy('label')
                ->pluck('jumlah', 'label');

            // 2. Inisialisasi nilai default aman
            $accuracy = 0.0;
            $confusionMatrix = [];
            $classificationReport = [];

            // Buat template struktur report default agar UI tidak kosong
            foreach ($labelStats as $label => $jumlah) {
                // Pastikan key berupa string bersih tanpa spasi
                $cleanLabel = strtoupper(trim((string)$label)); 
                $classificationReport[$cleanLabel] = [
                    'precision' => 0,
                    'recall'    => 0,
                    'f1-score'  => 0,
                    'support'   => $jumlah
                ];
            }

            // 3. BACA FILE FISIK METADATA DARI DISK STORAGE
            if (File::exists($this->metaPath)) {
                try {
                    $jsonContent = File::get($this->metaPath);
                    $flaskData = json_decode($jsonContent, true);

                    if (is_array($flaskData)) {
                        // AMAN: Ambil akurasi dan matriks di awal agar tidak terganggu proses perulangan jika crash
                        $accuracy = isset($flaskData['accuracy']) ? (float)$flaskData['accuracy'] : 0.0;
                        $confusionMatrix = $flaskData['confusion_matrix'] ?? [];
                        
                        // Mapping classification report secara hati-hati
                        if (!empty($flaskData['classification_report'])) {
                            foreach ($flaskData['classification_report'] as $labelKey => $metrics) {
                                // Paksa key menjadi string murni (mengatasi masalah key integer 0-9 dari Python)
                                $stringLabel = strtoupper(trim((string)$labelKey));

                                if (is_array($metrics)) {
                                    // Ambil nilai support asli dari database jika tersedia, jika tidak gunakan dari json
                                    $supportCount = $labelStats[$stringLabel] ?? ($metrics['support'] ?? 0);

                                    $classificationReport[$stringLabel] = [
                                        'precision' => $metrics['precision'] ?? 0,
                                        'recall'    => $metrics['recall'] ?? 0,
                                        'f1-score'  => $metrics['f1-score'] ?? 0,
                                        'support'   => $supportCount
                                    ];
                                }
                            }
                        }
                    }
                } catch (\Exception $flaskEx) {
                    // Jika parser report bermasalah, log error-nya tapi jangan gagalkan akurasi & matriks
                    Log::error('Gagal parsing isi detail file meta_model.json: ' . $flaskEx->getMessage());
                }
            } else {
                Log::warning('File metadata belum siap / tidak ditemukan di path: ' . $this->metaPath);
            }

            // 4. Kembalikan response sukses dengan data utuh ke Dashboard
            return response()->json([
                'status'                => 'success',
                'total'                 => $totalData,
                'total_data'            => $totalData,
                'total_labels'          => $totalLabels,
                'stats'                 => $labelStats,
                'accuracy'              => $accuracy,            // Nilai asli hasil training (tidak akan 0 lagi)
                'confusion_matrix'      => $confusionMatrix,    // Matriks konfusi utuh
                'classification_report' => $classificationReport,
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