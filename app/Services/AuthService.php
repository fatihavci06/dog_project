<?php

namespace App\Services;

use App\Mail\VerifyEmailMail;
use App\Models\RefreshToken;
use App\Models\User;
use Carbon\Carbon;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Illuminate\Auth\Events\Verified;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Str;

class AuthService
{
    protected $jwtSecret;

    public function __construct()
    {
        $this->jwtSecret = env('JWT_SECRET', 'your-secret-key'); // .env dosyasında JWT_SECRET
    }
    public function register(array $data)
    {
        return DB::transaction(function () use ($data) {
            $user = new User();
            $user->name = $data['name'];
            $user->email = $data['email'];
            $user->password = bcrypt($data['password']);
            $user->status = 'active';
            $user->save(); // burada ID oluşur

            // Pivot tablosuna role ekle
            $user->roles()->attach(2);

            $verificationUrl = URL::temporarySignedRoute(
                'verification.verify', // route ismi
                Carbon::now()->addMinutes(60), // link geçerlilik süresi
                [
                    'id' => $user->id,
                    'hash' => sha1($user->email)
                ]
            );
            Mail::to($user->email)->send(new VerifyEmailMail($user));

            // User objesi ve verification URL döndür
            return [
                'user' => $user,
                'verification_url' => $verificationUrl
            ];
        });
    }
    public function verifyEmail(int $id, string $hash)
    {
        $user = User::findOrFail($id);

        // hash kontrolü
        if (! hash_equals((string) $hash, sha1($user->email))) {
            throw new \Exception('Geçersiz doğrulama linki.');
        }

        // zaten doğrulanmış mı
        if ($user->hasVerifiedEmail()) {

            return $user; // zaten doğrulanmış, kullanıcı objesi dönebilir
        }

        // e-posta doğrulama ve kullanıcıyı aktif yap
        $user->markEmailAsVerified();
        $user->status = 'active';
        $user->save();

        event(new Verified($user));

        return $user;
    }
    public function login(array $credentials)
    {
        $user = User::where('email', $credentials['email'])->first();

        if (! $user || ! Hash::check($credentials['password'], $user->password)) {
            throw new \Exception('E-posta veya şifre yanlış');
        }

        // Access token üret
        $accessToken = $this->generateAccessToken($user);

        // Refresh token üret
        $rawRefresh = bin2hex(random_bytes(64));
        $hash = hash('sha256', $rawRefresh);

        RefreshToken::create([
            'user_id'    => $user->id,
            'token_hash' => $hash,
            'expires_at' => now()->addDays(30),
        ]);

        return [
            'access_token'  => $accessToken,
            'refresh_token' => $rawRefresh,
            'token_type'    => 'bearer',
            'expires_in'    => 15 * 60,
        ];
    }
    protected function generateAccessToken(User $user)
    {
        $now = time();
        $exp = $now + (15 * 60); // 15 dakika, ihtiyaca göre değiştirin
        $payload = [
            'iss' => config('app.url'),
            'user_id' => $user->id,
            'iat' => $now,
            'exp' => $exp,
            'jti' => (string) Str::uuid(),
            // 'scope' => 'user' // opsiyonel
        ];

        return JWT::encode($payload, env('JWT_SECRET'), 'HS256');
    }
    public function refresh(string $incomingToken): array
    {

        $hash   = hash('sha256', $incomingToken);
        $record = RefreshToken::where('token_hash', $hash)->first();

        if (! $record || $record->revoked || now()->gt($record->expires_at)) {
            throw new \Exception('Invalid refresh token');
        }

        // eski refresh token iptal
        $record->update(['revoked' => true]);

        // yeni refresh token üret
        $newRaw  = bin2hex(random_bytes(64));
        $newHash = hash('sha256', $newRaw);

        RefreshToken::create([
            'user_id'    => $record->user_id,
            'token_hash' => $newHash,
            'expires_at' => now()->addDays(30),
        ]);

        $accessToken = $this->generateAccessToken($record->user);

        return [
            'access_token'  => $accessToken,
            'refresh_token' => $newRaw,
            'token_type'    => 'bearer',
            'expires_in'    => 15 * 60,
        ];
    }
    public function decodeToken(string $token)
    {
        return JWT::decode($token, new Key($this->jwtSecret, 'HS256'));
    }
    public function logout(string $incomingToken): void
    {
        $hash   = hash('sha256', $incomingToken);
        $record = RefreshToken::where('token_hash', $hash)->first();

        if ($record) {
            $record->update(['revoked' => true]);
        }


        RefreshToken::where('user_id', $record->user_id)->update(['revoked' => true]);
    }
}
