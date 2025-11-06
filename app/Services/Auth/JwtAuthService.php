<?php

namespace App\Services\Auth;

use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use App\Models\User;
use Tymon\JWTAuth\Facades\JWTAuth;

class JwtAuthService
{
    public function issueToken(string $email, string $password): array
    {
        $creds = ['email' => $email, 'password' => $password, 'is_active' => true];

        if (! $token = Auth::guard('api')->attempt($creds)) {
            throw ValidationException::withMessages(['email' => 'Invalid credentials.']);
        }

        /** @var User $user */
        $user = Auth::guard('api')->user();
        $user->forceFill(['last_login_at' => now()])->save();

        return [
            'access_token' => $token,
            'token_type'   => 'bearer',
            'expires_in'   => Auth::guard('api')->factory()->getTTL() * 60,
        ];
    }

    public function refresh(string $token): array
    {
        $new = Auth::guard('api')->refresh(true, true); // invalidate old, refresh
        return [
            'access_token' => $new,
            'token_type'   => 'bearer',
            'expires_in'   => Auth::guard('api')->factory()->getTTL() * 60,
        ];
    }

    public function invalidate(string $token): void
    {
        JWTAuth::setToken($token)->invalidate(true);
    }

    public function userFrom(string $token): ?User
    {
        return JWTAuth::setToken($token)->authenticate();
    }
}
