<?php

namespace App\Http\Controllers;

use App\Http\Requests\ChangePasswordRequest;
use App\Http\Requests\loginRequest;
use App\Http\Requests\ProfileUpdateRequest;
use App\Http\Requests\RegisterRequest;
use App\Models\User;
use App\Models\UserDog;
use App\Services\AuthService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class AuthController extends ApiController
{
    //
    protected $authService;
    public function __construct(AuthService $authService)
    {
        $this->authService = $authService;
    }
    public function changePassword(ChangePasswordRequest $request)
{
    $user = User::find($request->user_id);

  return  $this->authService->changePassword($user, $request->validated());


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
                $activeUsersCount = User::where('status', 'active')->count();

                // Aktif köpek sayısı (status = 'active')
                $activeDogsCount = UserDog::count();
                $dogOwnersCount = User::where('role_id', 3)->where('status', 'active')->count();

                // Dog Adoption Seekers (role_id = 4)
                $adoptionSeekersCount = User::where('role_id', 4)->where('status', 'active')->count();

                return view('dashboard', compact('activeUsersCount', 'activeDogsCount', 'dogOwnersCount', 'adoptionSeekersCount'));
            }

            // admin değilse çıkış yap
            Auth::logout();

            return redirect()->back()->withErrors([
                'email' => 'Bu hesaba giriş yetkiniz yok.'
            ]);
        }

        return redirect()->back()->withErrors([
            'email' => 'Email or password is incorrect.'
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
        $this->authService->register($request->all());
    }
    public function register2(RegisterRequest $request)
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
            return $this->authService->login($request->only('email', 'password'));
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
            return $tokens = $this->authService->refresh($data['refresh_token']);
        } catch (\Exception $e) {
            return response()->json([
                'message' => $e->getMessage(),
            ], 401);
        }
    }
    public function forgotPassword(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation error',
                'errors' => $validator->errors(),
            ], 422);
        }

        return $result = $this->authService->forgotPassword($request->input('email'));
    }

    public function resetPassword(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'token' => 'required|string',
            'password' => 'required|string|min:6|confirmed',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation error',
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            $data = $validator->validated();

            $result = $this->authService->resetPassword(
                $data['email'],
                $data['token'],
                $data['password']
            );

            return response()->json($result);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 400);
        }
    }
    public function showResetPasswordForm(Request $request)
    {
        // ?token=...&email=... query paramlarıyla gelir
        $token = $request->query('token');
        $email = $request->query('email');

        return view('auth.reset-password', compact('token', 'email'));
    }

    public function resetPasswordSubmit(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'token' => 'required|string',
            'password' => 'required|string|min:8|confirmed',
        ]);

        try {
            $this->authService->resetPassword(
                $request->input('email'),
                $request->input('token'),
                $request->input('password')
            );

            return redirect()->back()->with('success', 'Şifreniz başarıyla değiştirildi.');
        } catch (\Exception $e) {
            return back()->withErrors(['message' => $e->getMessage()]);
        }
    }
    public function myProfile(Request $request)
    {
        return $this->authService->myProfile($request->user_id);
    }
    public function myProfileUpdate(ProfileUpdateRequest $request)
    {
        return $this->authService->updateProfile(
            User::find($request->user_id),
            $request->validated()
        );
    }
    public function deleteProfile(Request $request)
    {


        $this->authService->deleteUser($request->user_id);

        return response()->json([
            'success' => true,
            'message' => 'Your profile has been deleted successfully.'
        ]);
    }
}
