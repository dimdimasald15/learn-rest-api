<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Http\Requests\UserLoginRequest;
use App\Http\Requests\UserRegisterRequest;
use App\Http\Requests\UserUpdateRequest;
use App\Http\Resources\UserResource;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class UserController extends Controller
{
    public function register(UserRegisterRequest $request): JsonResponse
    {
        // Validasi data dari permintaan
        $data = $request->validated();

        // Periksa apakah username sudah ada di database
        if (User::where('username', $data['username'])->count() == 1) {
            // Jika username sudah terdaftar, lempar HttpResponseException dengan pesan kesalahan
            throw new HttpResponseException(response([
                "errors" => [
                    "username" => [
                        "Username already registered",
                    ]
                ]
            ], 400));
        }

        // Buat instance User baru dengan data yang telah divalidasi
        $user = new User($data);
        // Enkripsi password sebelum disimpan ke database
        $user->password = Hash::make($data['password']);
        // Simpan user baru ke database
        $user->save();

        // Kembalikan respon JSON dengan status 201 Created
        return (new UserResource($user))->response()->setStatusCode(201);
    }

    public function Login(UserLoginRequest $request): UserResource
    {
        // Validasi data dari permintaan
        $data = $request->validated();

        // Cari pengguna berdasarkan username yang diberikan
        $user = User::where('username', $data['username'])->first();

        // Periksa apakah pengguna ada dan password yang diberikan cocok dengan password yang tersimpan
        if (!$user || !Hash::check($data['password'], $user->password)) {
            // Jika pengguna tidak ada atau password salah, lempar HttpResponseException dengan pesan kesalahan
            throw new HttpResponseException(response([
                "errors" => [
                    "message" => [
                        "username or password wrong",
                    ]
                ]
            ], 401));
        }

        // Buat token unik untuk sesi pengguna menggunakan UUID
        $user->token = Str::uuid()->toString();

        // Simpan perubahan pada pengguna ke database
        $user->save();

        // Kembalikan UserResource baru dengan data pengguna
        return new UserResource($user);
    }

    public function get(Request $request): UserResource
    {
        // Mendapatkan pengguna yang sedang terautentikasi
        $user = Auth::user();

        // Mengembalikan respons JSON menggunakan UserResource dengan data pengguna yang sedang terautentikasi
        return new UserResource($user);
    }

    public function update(UserUpdateRequest $request): UserResource
    {
        // Validasi data dari permintaan
        $data = $request->validated();

        // Mendapatkan pengguna yang sedang terautentikasi
        $user = Auth::user();

        // Memperbarui nama pengguna jika data 'name' ada dalam request
        if (isset($data['name'])) {
            $user->name = $data['name'];
        }

        // Memperbarui password pengguna jika data 'password' ada dalam request
        if (isset($data['password'])) {
            $user->password = Hash::make($data['password']);
        }

        // Menyimpan perubahan pengguna ke database
        $user->save();

        // Mengembalikan respons JSON menggunakan UserResource dengan data pengguna yang telah diperbarui
        return new UserResource($user);
    }

    public function logout(Request $request): JsonResponse
    {
        // Mendapatkan pengguna yang sedang terautentikasi
        $user = Auth::user();

        // Menghapus token pengguna dengan mengatur nilainya ke null
        $user->token = null;

        // Menyimpan perubahan pada pengguna ke database
        $user->save();

        // Mengembalikan respons JSON dengan data true dan status kode 200 (OK)
        return response()->json([
            "data" => true
        ])->setStatusCode(200);
    }
}
