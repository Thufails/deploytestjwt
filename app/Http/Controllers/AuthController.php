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
        $validated = $this->validate($request, [
            'email' => 'required|exists:users,email',
            'password' => 'required'
        ]);

        $user = User::where('email', $validated['email'])->first();
        if (!Hash::check($validated['password'], $user->password)) {
            return response()->json([
                'success' => false,
                'message' => 'email or password incorrect',
            ], 401);
        }
        $payload = [
            'iat' => intval(microtime(true)),
            'exp' => intval(microtime(true)) + (60 * 60 * 1000),
            'uid' => $user->id
        ];
        // $algorithm = 'HS256'; (optional)
        $token = JWT::encode($payload, env('JWT_SECRET'), 'HS256');

        if (!$token) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to generate token',
            ], 500);
        }
        // Save token to database
        $user->jwt_token = $token;
        $user->save();
        if ($token) {
            return response()->json([
                'success' => true,
                'message' => 'Successfully Login',
                'access_token' => $token
            ], 201);
        } else {
            return response()->json([
                'success' => false,
                'message' => 'Login Failed',
                'data' => ''
            ], 400);
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
