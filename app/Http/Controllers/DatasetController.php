<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DatasetController extends Controller
{
    /**
     * Menampilkan halaman dataset.
     */
    public function index()
    {
        return view('admin.dataset');
    }

    /**
     * Mengambil statistik data.
     */
    public function getStats()
    {
        try {
            $totalRows = DB::table('datasets')->count();

            if ($totalRows === 0) {
                return response()->json([
                    'status' => 'info',
                    'message' => 'Dataset saat ini kosong.',
                    'stats' => [],
                    'total' => 0
                ]);
            }

            $statsData = DB::table('datasets')
                ->select('label', DB::raw('count(*) as total'))
                ->groupBy('label')
                ->get();

            $stats = [];
            foreach ($statsData as $row) {
                $stats[$row->label] = $row->total;
            }

            return response()->json([
                'status' => 'success',
                'stats' => $stats,
                'total' => $totalRows
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Gagal mengambil statistik: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Menghapus label spesifik.
     */
    public function deleteLabel(string $label)
    {
        try {
            $decodedLabel = urldecode($label);
            
            $exists = DB::table('datasets')->where('label', $decodedLabel)->exists();
            if (!$exists) {
                return response()->json(['status' => 'error', 'message' => 'Label tidak ditemukan.'], 404);
            }

            DB::table('datasets')->where('label', $decodedLabel)->delete();

            return response()->json([
                'status' => 'success',
                'message' => "Seluruh sampel dengan label '{$decodedLabel}' berhasil dihapus."
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Gagal menghapus label: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Mengosongkan seluruh dataset.
     */
    public function clearDataset()
    {
        try {
            if (DB::table('datasets')->count() === 0) {
                return response()->json([
                    'status' => 'error', 
                    'message' => 'Dataset sudah kosong, tidak ada data yang perlu dihapus.'
                ], 400);
            }

            DB::table('datasets')->truncate();

            return response()->json([
                'status' => 'success',
                'message' => 'Seluruh data pada dataset berhasil dikosongkan permanen.'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Gagal mengosongkan dataset: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Download Dataset (Mendukung format JSON & SQL).
     */
    public function download(Request $request)
    {
        $format = $request->query('format', 'json');
        $total = DB::table('datasets')->count();

        if ($total === 0) {
            if ($request->ajax()) {
                return response()->json(['message' => 'Dataset kosong'], 400);
            }
            return redirect()->back()->with('error', 'Dataset kosong! Tidak ada data untuk diunduh.');
        }

        $allData = DB::table('datasets')->get();
        $timestamp = date('Y-m-d_H-i-s');

        // Menampung data yang sudah dibersihkan dari ID utama
        $sanitizedData = [];
        foreach ($allData as $row) {
            $arrayRow = (array) $row;
            
            // ANTI BENTROK: Buang id_dataset & id agar database target menggunakan AUTO_INCREMENT
            unset($arrayRow['id_dataset']); 
            unset($arrayRow['id']); 
            
            $sanitizedData[] = $arrayRow;
        }

        if (strtolower($format) === 'sql') {
            // Generasi file SQL dump khusus untuk tabel datasets
            $sqlContent = "-- SignNet Project Database Dump\n";
            $sqlContent .= "-- Generated: " . date('Y-m-d H:i:s') . "\n\n";
            
            foreach ($sanitizedData as $arrayRow) {
                $columns = implode(", ", array_map(fn($c) => "`$c`", array_keys($arrayRow)));
                $values = implode(", ", array_map(function($v) {
                    if (is_null($v)) return 'NULL';
                    return "'" . addslashes($v) . "'";
                }, array_values($arrayRow)));

                $sqlContent .= "INSERT INTO `datasets` ($columns) VALUES ($values);\n";
            }

            $filename = "sign_language_dataset_{$timestamp}.sql";
            return response($sqlContent, 200)
                ->header('Content-Type', 'application/sql')
                ->header('Content-Disposition', "attachment; filename=\"{$filename}\"");
        }

        // Default: Format JSON (Sekarang sudah menggunakan $sanitizedData yang bersih dari ID)
        $jsonContent = json_encode($sanitizedData, JSON_PRETTY_PRINT);
        $filename = "sign_language_dataset_{$timestamp}.json";

        return response($jsonContent, 200)
            ->header('Content-Type', 'application/json')
            ->header('Content-Disposition', "attachment; filename=\"{$filename}\"");
    }

    /**
     * Import Dataset (Mendukung Payload format JSON & SQL raw text).
     */
     public function import(Request $request)
    {
        try {
            $format = $request->json('format');
            $content = $request->json('content');

            if (empty($content)) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'File data kosong atau tidak terbaca.'
                ], 400);
            }

            DB::beginTransaction();

            if ($format === 'sql') {
                // Bersihkan string SQL dan pisahkan berdasarkan kueri kustom
                $queries = array_filter(array_map('trim', explode(";\n", $content)));
                $totalImported = 0;

                foreach ($queries as $query) {
                    if (!empty($query) && str_starts_with(strtoupper($query), 'INSERT')) {
                        DB::unprepared($query . ';');
                        $totalImported++;
                    }
                }

                DB::commit();

                return response()->json([
                    'status' => 'success',
                    'message' => "Berhasil mengeksekusi kueri SQL dan menyinkronkan data sampel baru."
                ]);
            } 

            // Penanganan Format JSON
            if (!is_array($content)) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Struktur konten JSON utama harus berupa array objek.'
                ], 400);
            }

            $dataToInsert = [];
            foreach ($content as $row) {
                if (!isset($row['label'])) {
                    continue; 
                }

                $newData = [];
                foreach ($row as $key => $value) {
                    // PERBAIKAN: Sisi import JSON juga menyaring 'id_dataset' & 'id'
                    if ($key !== 'id_dataset' && $key !== 'id') {
                        // Otomatis ubah kembali landmarks array/object menjadi format string JSON database
                        $newData[$key] = is_array($value) || is_object($value) ? json_encode($value) : $value;
                    }
                }
                $dataToInsert[] = $newData;
            }

            if (empty($dataToInsert)) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Tidak ada baris koordinat data valid yang ditemukan di dalam file JSON.'
                ], 400);
            }

            // Pecah transaksi ke chunking per 500 baris demi performa alokasi memori
            $chunks = array_chunk($dataToInsert, 500);
            foreach ($chunks as $chunk) {
                DB::table('datasets')->insert($chunk);
            }

            DB::commit();
            $totalImported = count($dataToInsert);

            return response()->json([
                'status' => 'success',
                'message' => "Berhasil mengimpor {$totalImported} sampel data ke dalam dataset."
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status' => 'error',
                'message' => 'Gagal memproses import data: ' . $e->getMessage()
            ], 500);
        }
    }
}