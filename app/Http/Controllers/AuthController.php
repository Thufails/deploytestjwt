<?php

namespace App\Http\Controllers;

use App\Models\User;
use Firebase\JWT\JWT;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        //$this->middleware('auth:api',['except'=>['login', 'register']]);
    }

    public function register(Request $request)
    {
        $validated = $this->validate($request, [
            'name' => 'required|max:255',
            'email' => 'required|email|max:255|unique:users,email',
            'password' => 'required|min:6'
        ]);
        $user = new User();
        $user->name = $validated['name'];
        $user->email = $validated['email'];
        $user->password = Hash::make($validated['password']);
        $user->save();
        if ($user) {
            return response()->json([
                'success' => true,
                'message' => 'Successfully Registered',
                'data' => $user
            ], 201);
        } else {
            return response()->json([
                'success' => false,
                'message' => 'Registration Failed',
                'data' => null
            ], 400);
        }
    }

    public function login(Request $request)
    {
            // Validasi input
    $validated = $this->validate($request, [
        'email' => 'required|email|exists:users,email',
        'password' => 'required'
    ]);

    // Ambil user berdasarkan email
    $user = User::where('email', $validated['email'])->first();

    // Periksa kecocokan password
    if (!$user || !Hash::check($validated['password'], $user->password)) {
        return response()->json([
            'success' => false,
            'message' => 'Email or password incorrect',
        ], 401);
    }

    // Set waktu kedaluwarsa token (misalnya, 6 jam)
    $expirationTimeInSeconds = 6 * 60 * 60; // 6 jam dalam detik

    // Hitung waktu kedaluwarsa dalam detik sejak epoch
    $expirationTime = time() + $expirationTimeInSeconds;

    // Konversi durasi waktu kedaluwarsa menjadi format jam
    $expiresInHours = $expirationTimeInSeconds / 3600;

    // Buat payload JWT
    $payload = [
        'iat' => time(),
        'exp' => $expirationTime,
        'uid' => $user->id
    ];

    try {
        // Encode payload menjadi token JWT
        $token = JWT::encode($payload, env('JWT_SECRET'), 'HS256');

        // Simpan token ke dalam user (opsional)
        $user->jwt_token = $token;
        $user->save();

        // Tanggapan sukses
        return response()->json([
            'success' => true,
            'message' => 'Successfully logged in',
            'access_token' => $token,
            'token_expired' => 'Kadaluwarsa dalam '.$expiresInHours . ' jam',
        ], 200);
    } catch (\Exception $e) {
        // Tanggapan jika gagal menghasilkan token
        return response()->json([
            'success' => false,
            'message' => 'Failed to generate token',
        ], 500);
    }
    }

    public function showUser()
    {

        if (!Auth::check()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized',
                'data' => []
            ], 401);
        }

        $user = Auth::user();
        return response()->json([
            'success' => true,
            'message' => 'Profile has been Showed',
            'data' => $user,
        ], 200);
    }
}
