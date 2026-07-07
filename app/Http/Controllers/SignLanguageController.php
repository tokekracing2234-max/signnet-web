<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class SignLanguageController extends Controller
{
    private $flaskUrl = 'http://127.0.0.1:5000'; 

    public function index()
    {
        return view('admin.trainmodel');
    }

    public function collect(Request $request)
    {
        try {
            $response = Http::post("{$this->flaskUrl}/collect", $request->all());
            return response()->json($response->json(), $response->status());
        } catch (\Exception $e) {
            Log::error('Gagal mengosongkan/mengirim data ke Flask: ' . $e->getMessage());
            return response()->json(['status' => 'error', 'message' => 'Backend Flask tidak merespons.'], 500);
        }
    }

    public function train()
    {
        try {
            $response = Http::post("{$this->flaskUrl}/train");
            return response()->json($response->json(), $response->status());
        } catch (\Exception $e) {
            Log::error('Gagal memicu training di Flask: ' . $e->getMessage());
            return response()->json(['status' => 'error', 'message' => 'Gagal terhubung ke service training.'], 500);
        }
    }
}