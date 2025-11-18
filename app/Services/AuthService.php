<?php

namespace App\Services;

use App\Http\Resources\UserProfileResource;
use App\Mail\ResetPasswordMail;
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
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Str;

class AuthService
{
    protected $jwtSecret;

    public function __construct()
    {
        $this->jwtSecret = env('JWT_SECRET', 'your-secret-key'); // .env dosyasında JWT_SECRET
    }
    public function changePassword($user, array $data)
{
    return DB::transaction(function () use ($user, $data) {

        /* ------------------------------------------------
           1) CURRENT PASSWORD DOĞRU MU?
        ------------------------------------------------ */
        if (!Hash::check($data['current_password'], $user->password)) {
            throw new \Exception('Current password is incorrect.', 422);
        }

        /* ------------------------------------------------
           2) YENİ ŞİFRE ESKİ ŞİFRE İLE AYNI MI?
        ------------------------------------------------ */
        if (Hash::check($data['new_password'], $user->password)) {
            throw new \Exception('New password cannot be the same as the old password.', 422);
        }

        /* ------------------------------------------------
           3) ŞİFREYİ GÜNCELLE
        ------------------------------------------------ */
        $user->password = bcrypt($data['new_password']);
        $user->save();

        /* ------------------------------------------------
           4) TÜM TOKENLARI SİL → YENİDEN GİRİŞ ZORUNLU
        ------------------------------------------------ */
       RefreshToken::where('user_id',$user->id)->delete();

        return true;
    });
}
    public function register(array $data)
    {
        return DB::transaction(function () use ($data) {

            /* ---------------- USER CREATE ---------------- */

            $user = User::create([
                'name'           => $data['fullname'],
                'email'          => $data['email'],
                'password'       => bcrypt($data['password']),
                'role_id'        => $data['role'],
                'status'         => 'active',
                'privacy_policy' => $data['privacy_policy'],
                'newlestter'     => $data['newlestter'] ?? 0,
            ]);

            // User'ın pivot rolu (RBAC için)
            $user->roles()->attach($data['role']);

            /* ---------------- VERIFY EMAIL ---------------- */

            $verificationUrl = URL::temporarySignedRoute(
                'verification.verify',
                Carbon::now()->addMinutes(60),
                [
                    'id'   => $user->id,
                    'hash' => sha1($user->email),
                ]
            );

            Mail::to($user->email)->send(new VerifyEmailMail($user));

            /* ---------------- PUP PROFILE CREATE ---------------- */
            if (isset($data['pup_profile'])) {
                app(PupProfileService::class)
                    ->createPupProfileForUser($user, $data['pup_profile']);
            }

            return [
                'user'              => $user,
                'verification_url'  => $verificationUrl,
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
            throw new \Exception('Email or password is incorrect');
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

        ];
    }
    protected function generateAccessToken(User $user)
    {
        $now = time();
        $exp = $now + (240 * 60); // 15 dakika, ihtiyaca göre değiştirin
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

    public function forgotPassword(string $email)
    {
        $user = User::where('email', $email)->first();
        if (!$user) {
            throw new \Exception('User not found', 404);
        }

        $token = Str::random(60);

        DB::table('password_resets')->updateOrInsert(
            ['email' => $email],
            ['token' => $token, 'created_at' => now()]
        );

        Mail::to($email)->send(new ResetPasswordMail($token, $email));

        return ['message' => 'A password reset link has been sent to your email address.'];
    }

    public function resetPassword(string $email, string $token, string $password)
    {
        $record = DB::table('password_resets')->where([
            'email' => $email,
            'token' => $token,
        ])->first();

        if (!$record) {
            throw new \Exception('Geçersiz veya süresi dolmuş token.');
        }

        // Token 60 dakikadan eskiyse geçersiz
        if (now()->diffInMinutes($record->created_at) > 60) {
            throw new \Exception('Token süresi dolmuş.');
        }

        $user = User::where('email', $email)->firstOrFail();
        $user->password = Hash::make($password);
        $user->save();

        // Tokeni sil
        DB::table('password_resets')->where('email', $email)->delete();

        return ['message' => 'Şifre başarıyla güncellendi.'];
    }
    public function deleteUser($userId)
    {
        $user=User::find($userId);
        return DB::transaction(function () use ($user) {

            /* ----------------------------

            /* ----------------------------
           Soft Delete user
        ---------------------------- */
            $user->delete();

            /* ----------------------------
           Tokenları iptal et (logout)
        ---------------------------- */
            $user->refreshTokens()->delete();

            return true;
        });
    }

    public function myProfile($userId)
    {
        $user = User::find($userId);
        return [
            'id'            => $user->id,
            'fullname'      => $user->name,
            'email'         => $user->email,
            'date_of_birth' => $user->date_of_birth,
            'gender'        => $user->gender,
            'country'       => $user->country,
            'photo_url'     => $user->photo_url,

        ];
    }
    public function updateProfile($user, array $data)
    {
        return DB::transaction(function () use ($user, $data) {

            /* ----------------------------------
           BASIC USER UPDATE (FULLNAME!)
        ---------------------------------- */
            $user->update([
                'name'      => $data['fullname'] ?? $user->fullname,
                'date_of_birth' => $data['date_of_birth'] ?? $user->date_of_birth,
                'gender'        => $data['gender'] ?? $user->gender,
                'country'       => $data['country'] ?? $user->country,
            ]);

            /* ----------------------------------
           PROFILE PHOTO UPDATE
        ---------------------------------- */
            if (!empty($data['photo'])) {

                $imageData = preg_replace('#^data:image/\w+;base64,#i', '', $data['photo']);
                $imageData = str_replace(' ', '+', $imageData);

                $fileName = 'users/' . Str::uuid() . '.jpg';

                Storage::disk('public')->put($fileName, base64_decode($imageData));

                // eski fotoğrafı sil (varsa)
                if ($user->photo && Storage::disk('public')->exists($user->photo)) {
                    Storage::disk('public')->delete($user->photo);
                }

                $user->photo = $fileName;
                $user->save();
            }

            return $user->fresh();
        });
    }
}
