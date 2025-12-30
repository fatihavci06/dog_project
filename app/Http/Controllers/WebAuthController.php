<?php

namespace App\Http\Controllers;

use App\Http\Requests\loginRequest;
use App\Http\Requests\ProfileUpdateRequest;
use App\Http\Requests\RegisterRequest;
use App\Models\User;
use App\Models\UserDog;
use App\Services\AuthService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class WebAuthController extends Controller
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
               return redirect()->route('dashboard');
            }

            // admin değilse çıkış yap
            Auth::logout();

            return redirect()->back()->withErrors([
                'email' => 'You do not have access to this account.'
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
}
