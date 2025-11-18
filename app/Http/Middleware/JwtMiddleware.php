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
         Log::info('=== JWT MIDDLEWARE TETİKLENDİ ===');

        $authHeader = $request->header('Authorization');

        if (!$authHeader || !preg_match('/Bearer\s(\S+)/', $authHeader, $matches)) {
            return response()->json([
                'message' => 'Authorization token not found.'
            ], 401);
        }

        $token = $matches[1];

        try {
            // Burada secret .env’den alınıyor
            $secret = config('app.jwt_secret', env('JWT_SECRET'));
            $decoded = JWT::decode($token, new Key($secret, 'HS256'));

            // decoded içinden user_id çekip request’e ekleyelim
            $request->merge([
                'user_id' => $decoded->user_id ?? null
            ]);

            $roleId = User::find($decoded->user_id)->role_id;

            if(empty($roleId) && $roleId)
            {
                 $request->merge(['role_id' => $roleId]);
            Log::info($roleId);
            }

        } catch (Exception $e) {

            return response()->json([
                'message' => 'Invalid or expired token.',
                'error'   => $e->getMessage()
            ], 401);
        }

        return $next($request);
    }
}
