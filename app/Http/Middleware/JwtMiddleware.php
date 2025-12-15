<?php

namespace App\Http\Middleware;

use App\Models\User;
use Closure;
use Exception;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class JwtMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        $authHeader = $request->header('Authorization');

        if (!$authHeader || !preg_match('/Bearer\s(\S+)/', $authHeader, $matches)) {
            return response()->json([
                'message' => 'Authorization token not found.'
            ], 401);
        }

        $token = $matches[1];

        /**
         * 🔐 JWT AYARLARI (DİREKT MIDDLEWARE İÇİNDE)
         */
        $secret = env('JWT_SECRET');      // .env’den okunur
        $algo   = 'HS256';                // Sabit
        $ttl    = 60 * 60 * 24;            // 1 gün (opsiyonel)

        if (!$secret) {
            Log::error('JWT_SECRET is missing in .env');
            return response()->json([
                'message' => 'JWT configuration error.'
            ], 500);
        }

        // try {
            $decoded = JWT::decode($token, new Key($secret, $algo));

            // Kullanıcıyı bul
            $user = User::find($decoded->user_id ?? null);

            if (!$user) {
                return response()->json([
                    'message' => 'User not found.'
                ], 401);
            }

            // Request içine ekle
            $request->merge([
                'user_id' => $user->id,
                'role_id' => $user->role_id,
            ]);

            // Dil
            app()->setLocale($decoded->language ?? 'en');

        // } catch (Exception $e) {
        //     Log::warning('JWT verification failed', [
        //         'error' => $e->getMessage()
        //     ]);

        //     return response()->json([
        //         'message' => 'Invalid or expired token.'
        //     ], 401);
        // }

        return $next($request);
    }
}
