<?php

namespace App\Http\Controllers;

use App\Http\Requests\loginRequest;
use App\Http\Requests\RegisterRequest;
use App\Models\User;
use App\Services\AuthService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class AuthController extends Controller
{
    //
    protected $authService;
    public function __construct(AuthService $authService)
    {
        $this->authService = $authService;
    }
    public function login(Request $request)
    {
        $credentials = $request->only('email', 'password');

        if (Auth::attempt($credentials)) {
            $userId = Auth::id();

            // role_user tablosunda user_id = 1 ve role_id = 1 var mı?
            $isAdmin = DB::table('role_user')
                ->where('user_id', $userId)
                ->where('role_id', 1)
                ->exists();

            if ($isAdmin) {
                return view('welcome');
            }

            // admin değilse çıkış yap
            Auth::logout();

            return redirect()->back()->withErrors([
                'email' => 'Bu hesaba giriş yetkiniz yok.'
            ]);
        }

        return redirect()->back()->withErrors([
            'email' => 'Email veya şifre hatalı.'
        ]);
    }
    public function logout()
    {
        Auth::logout();
        return view('auth.login');
    }
   public function logoutApi(Request $request)
{
    $validator = Validator::make($request->all(), [
        'refresh_token' => 'required|string',
    ]);

    if ($validator->fails()) {
        return response()->json([
            'message' => 'Validation error',
            'errors'  => $validator->errors(),
        ], 422);
    }

    try {
        $data = $validator->validated();

        $this->authService->logout($data['refresh_token']);

        return response()->json(['message' => 'Logged out successfully']);
    } catch (\Exception $e) {
        return response()->json(['message' => $e->getMessage()], 400);
    }
}

    public function register(RegisterRequest $request)
    {
        try {
            // Kullanıcıyı register et (tüm işlemler servis içinde yapılacak)
            $user = $this->authService->register($request->all());

            return response()->json([
                'status' => 'success',
                'message' => 'Kullanıcı başarıyla oluşturuldu. Lütfen e-posta adresinizi doğrulayın.',
                'user' => $user
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Kullanıcı oluşturulamadı.',
                'error' => $e->getMessage() // production ortamında gizlenebilir
            ], 500);
        }
    }
    public function verifyEmail($id, $hash)
    {
        try {
            $result = $this->authService->verifyEmail($id, $hash);

            return response()->json([
                'status' => 'success',
                'message' => 'E-posta doğrulandı, artık giriş yapabilirsiniz.',
                'user' => $result
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage()
            ], 400);
        }
    }
    public function loginApi(loginRequest $request)
    {


        try {
            $data = $this->authService->login($request->only('email', 'password'));

            return response()->json([
                'status' => 'success',
                'message' => 'Giriş başarılı',
                'data' => $data
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage()
            ], 401);
        }
    }
    public function refresh(Request $request)
{
    $validator = Validator::make($request->all(), [
        'refresh_token' => 'required|string',
    ]);

    if ($validator->fails()) {
        return response()->json([
            'message' => 'Validation error',
            'errors'  => $validator->errors(),
        ], 422);
    }

    try {
        $data   = $validator->validated();
        $tokens = $this->authService->refresh($data['refresh_token']);

        return response()->json($tokens);
    } catch (\Exception $e) {
        return response()->json([
            'message' => $e->getMessage(),
        ], 401);
    }
}
}
