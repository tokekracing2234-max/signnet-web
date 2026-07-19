<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;

class ModelSyncController extends Controller
{
    // Sama dengan SignController::KATEGORI_LIST. Kalau kamu memang punya
    // dua controller ini aktif berbarengan (cek routes/api.php & web.php),
    // sebaiknya salah satu dihapus supaya tidak ada dua sumber kebenaran
    // untuk endpoint yang sama. Konstanta ini sengaja diduplikasi di sini
    // supaya file tetap bisa berdiri sendiri sampai kamu memutuskan mana
    // yang dipakai.
    private const KATEGORI_LIST = ['huruf', 'angka'];

    public function receiveModel(Request $request)
    {
        // 1. Validasi (cek file + kategori)
        if (!$request->hasFile('onnx_model') || !$request->hasFile('meta_model') || !$request->hasFile('labels')) {
            return response()->json([
                'status' => 'error',
                'message' => 'Sinkronisasi gagal. File model (.onnx), metadata (.json), atau labels (.json) tidak ditemukan dalam request.'
            ], 400);
        }

        $kategori = $request->input('kategori');
        if (!in_array($kategori, self::KATEGORI_LIST, true)) {
            return response()->json([
                'status' => 'error',
                'message' => "Sinkronisasi gagal. Field 'kategori' wajib diisi salah satu dari: " . implode(', ', self::KATEGORI_LIST) . "."
            ], 400);
        }

        // 2. Tentukan target lokasi folder asset
        $publicModelsDirectory = public_path('models');
        $storageMetadataDirectory = storage_path('app/ai_metadata');

        if (!File::exists($publicModelsDirectory)) {
            File::makeDirectory($publicModelsDirectory, 0755, true);
        }

        if (!File::exists($storageMetadataDirectory)) {
            File::makeDirectory($storageMetadataDirectory, 0755, true);
        }

        try {
            $onnxFile = $request->file('onnx_model');
            $metaFile = $request->file('meta_model');
            $labelsFile = $request->file('labels');

            // Nama file diberi suffix kategori supaya huruf & angka tidak
            // saling menimpa.
            $onnxFile->move($publicModelsDirectory, "rf_model_{$kategori}.onnx.gz");
            $labelsFile->move($publicModelsDirectory, "labels_{$kategori}.json");
            $metaFile->move($storageMetadataDirectory, "meta_model_{$kategori}.json");

            file_put_contents(public_path("models/model_version_{$kategori}.txt"), time());

            return response()->json([
                'status' => 'success',
                'message' => "🚀 [BACKEND] Berhasil menerima asset kategori '{$kategori}'. rf_model_{$kategori}.onnx.gz & labels_{$kategori}.json disinkronkan ke public!"
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Gagal menulis file ke direktori server: ' . $e->getMessage()
            ], 500);
        }
    }
}