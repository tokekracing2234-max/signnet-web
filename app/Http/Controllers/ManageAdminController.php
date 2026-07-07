<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;

class ManageAdminController extends Controller
{
    /**
     * Menampilkan view halaman kelola admin
     */
    public function index()
    {
        return view('admin.kelolaadmin');
    }

    /**
     * Mengambil daftar data admin (Read) beserta info admin yang sedang aktif
     */
    public function list()
    {
        try {
            $admins = DB::table('admins')
                ->select('id_admin', 'username', 'email', 'created_at')
                ->orderBy('created_at', 'desc')
                ->get();

            $current_username = Auth::user()?->username ?: (Auth::guard('admin')->user()?->username ?: 'admin');

            return response()->json([
                'admins' => $admins,
                'current_username' => $current_username
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Gagal memuat data dari server: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Membuat data admin baru (Create) di tabel admins
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'username' => 'required|string|alpha_dash|max:50|unique:admins,username',
            'email' => 'required|email|max:255|unique:admins,email',
            'password' => 'required|string|min:6',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => $validator->errors()->first()
            ], 422);
        }

        try {
            DB::table('admins')->insert([
                'username' => $request->username,
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            return response()->json([
                'status' => 'success',
                'message' => 'Akun admin baru berhasil didaftarkan.'
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Terjadi kesalahan internal saat menyimpan data: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Memperbarui data admin (Update) di tabel admins
     */
    public function update(Request $request, string $id)
{
    $admin = DB::table('admins')->where('id_admin', $id)->first();

    if (!$admin) {
        return response()->json(['status' => 'error', 'message' => 'Data tidak ditemukan.'], 404);
    }

    // Validasi
    $validator = Validator::make($request->all(), [
        'username' => 'required|string|alpha_dash|max:50|unique:admins,username,' . $id . ',id_admin',
        'email' => 'required|email|max:255|unique:admins,email,' . $id . ',id_admin',
        'old_password' => 'required_with:password', // Wajib jika ada password baru
        'password' => 'sometimes|nullable|string|min:6',
    ]);

    if ($validator->fails()) {
        return response()->json(['status' => 'error', 'message' => $validator->errors()->first()], 422);
    }

    $updateData = [
        'username' => $request->username,
        'email' => $request->email,
        'updated_at' => now(),
    ];

    // LOGIKA PENGECEKAN PASSWORD LAMA
    if ($request->filled('password')) {
        if (!Hash::check($request->old_password, $admin->password)) {
            return response()->json(['status' => 'error', 'message' => 'Password lama salah!'], 422);
        }
        $updateData['password'] = Hash::make($request->password);
    }

    DB::table('admins')->where('id_admin', $id)->update($updateData);

    return response()->json(['status' => 'success', 'message' => 'Data berhasil diperbarui.'], 200);
}

    /**
     * Menghapus akun admin (Delete) dari tabel admins
     */
    public function destroy(string $id)
    {
        try {
            $admin = DB::table('admins')->where('id_admin', $id)->first();

            if (!$admin) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Akun admin tidak ditemukan.'
                ], 404);
            }

            if ($admin->username === 'admin') {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Akun administrator utama dilindungi dan tidak dapat dihapus dari sistem.'
                ], 403);
            }

            DB::table('admins')->where('id_admin', $id)->delete();

            return response()->json([
                'status' => 'success',
                'message' => 'Akses akun resmi dicabut.'
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Gagal menghapus data akibat masalah server: ' . $e->getMessage()
            ], 500);
        }
    }
}