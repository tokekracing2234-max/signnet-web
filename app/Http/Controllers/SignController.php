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
        $this->flaskUrl = 'https://signnet-ai-skripsi-anda.loca.lt';
        $this->metaPath = storage_path('app/ai_metadata/meta_model.json');
    }

    public function updateModelFiles(Request $request)
    {
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

            $request->file('onnx_model')->move($destinationPath, 'rf_model.onnx.gz');
            $request->file('labels')->move($destinationPath, 'labels.json');

            $storageMetadataDirectory = storage_path('app/ai_metadata');
            if (!File::exists($storageMetadataDirectory)) {
                File::makeDirectory($storageMetadataDirectory, 0755, true);
            }
            
            // flag
            $request->file('meta_model')->move($storageMetadataDirectory, 'meta_model.json');

            return response()->json(['status' => 'success', 'message' => 'Update berhasil'], 200);

        } catch (\Exception $e) {
            Log::error('updateModelFiles error: ' . $e->getMessage());
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 500);
        }
    }

    public function getLatestEvaluation()
    {
        try {
            if (File::exists($this->metaPath)) {
                $jsonContent = File::get($this->metaPath);
                $data = json_decode($jsonContent, true);

                return response()->json([
                    'status' => 'ready',
                    'accuracy' => $data['accuracy'] ?? 0.0,
                    'classification_report' => $data['classification_report'] ?? []
                ], 200);
            }

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

            if (File::exists($this->metaPath)) {
                File::delete($this->metaPath);
            }

            try {
                Http::timeout(2)->post("{$this->flaskUrl}/train-cloud");
            } catch (\Illuminate\Http\Client\ConnectionException $e) {

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

    public function getDashboardStats()
    {
        try {
            $totalData   = Datasets::count();
            $totalLabels = Datasets::distinct('label')->count('label');
            $labelStats  = Datasets::selectRaw('label, COUNT(*) as jumlah')
                ->groupBy('label')
                ->orderBy('label')
                ->pluck('jumlah', 'label');

            $accuracy = 0.0;
            $confusionMatrix = [];
            $classificationReport = [];

            foreach ($labelStats as $label => $jumlah) {
                $cleanLabel = strtoupper(trim((string)$label)); 
                $classificationReport[$cleanLabel] = [
                    'precision' => 0,
                    'recall'    => 0,
                    'f1-score'  => 0,
                    'support'   => $jumlah
                ];
            }

            if (File::exists($this->metaPath)) {
                try {
                    $jsonContent = File::get($this->metaPath);
                    $flaskData = json_decode($jsonContent, true);

                    if (is_array($flaskData)) {
                        $accuracy = isset($flaskData['accuracy']) ? (float)$flaskData['accuracy'] : 0.0;
                        $confusionMatrix = $flaskData['confusion_matrix'] ?? [];

                        if (!empty($flaskData['classification_report'])) {
                            foreach ($flaskData['classification_report'] as $labelKey => $metrics) {
                                $stringLabel = strtoupper(trim((string)$labelKey));

                                if (is_array($metrics)) {
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
                    Log::error('Gagal parsing isi detail file meta_model.json: ' . $flaskEx->getMessage());
                }
            } else {
                Log::warning('File metadata belum siap / tidak ditemukan di path: ' . $this->metaPath);
            }

            return response()->json([
                'status'                => 'success',
                'total'                 => $totalData,
                'total_data'            => $totalData,
                'total_labels'          => $totalLabels,
                'stats'                 => $labelStats,
                'accuracy'              => $accuracy,
                'confusion_matrix'      => $confusionMatrix,
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