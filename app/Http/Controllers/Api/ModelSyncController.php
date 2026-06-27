<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;

class ModelSyncController extends Controller
{
    public function receiveModel(Request $request)
    {
        // 1. Validasi memastikan ketiga file dikirim dengan benar oleh script Python
        if (!$request->hasFile('onnx_model') || !$request->hasFile('meta_model') || !$request->hasFile('labels')) {
            return response()->json([
                'status' => 'error',
                'message' => 'Sinkronisasi gagal. File model (.onnx), metadata (.json), atau labels (.json) tidak ditemukan dalam request.'
            ], 400);
        }

        // 2. Tentukan target lokasi folder asset
        $publicModelsDirectory = public_path('models');         // Untuk Frontend (Akses Cepat Browser)
        $storageMetadataDirectory = storage_path('app/ai_metadata'); // Untuk Backend / Dashboard Admin (Aman)

        // Buat folder public/models jika belum ada
        if (!File::exists($publicModelsDirectory)) {
            File::makeDirectory($publicModelsDirectory, 0755, true);
        }

        // Buat folder storage/app/ai_metadata jika belum ada
        if (!File::exists($storageMetadataDirectory)) {
            File::makeDirectory($storageMetadataDirectory, 0755, true);
        }

        try {
            $onnxFile = $request->file('onnx_model');
            $metaFile = $request->file('meta_model');
            $labelsFile = $request->file('labels');

            $onnxFile->move($publicModelsDirectory, 'rf_model.onnx.gz');
            $labelsFile->move($publicModelsDirectory, 'labels.json');
            $metaFile->move($storageMetadataDirectory, 'meta_model.json');

            // Simpan versi model untuk versioning cache browser
            file_put_contents(public_path('models/model_version.txt'), time());

            return response()->json([
                'status' => 'success',
                'message' => '🚀 [BACKEND] Berhasil menerima asset terkompresi. rf_model.onnx.gz & labels.json disinkronkan ke public!'
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Gagal menulis file ke direktori server: ' . $e->getMessage()
            ], 500);
        }
    }
}