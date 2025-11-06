<?php

namespace App\Services\Auth;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use App\Models\User;

class WebAuthService
{
    public function login(string $email, string $password, bool $remember = false): User
    {
        $key = $this->throttleKey($email);

        if (RateLimiter::tooManyAttempts($key, 5)) {
            $seconds = RateLimiter::availableIn($key);
            throw ValidationException::withMessages([
                'email' => "Too many attempts. Try again in {$seconds}s.",
            ]);
        }

        if (! Auth::attempt(['email' => $email, 'password' => $password, 'is_active' => true], $remember)) {
            RateLimiter::hit($key, 60);
            throw ValidationException::withMessages([
                'email' => 'Invalid credentials or inactive account.',
            ]);
        }

        RateLimiter::clear($key);

        /** @var User $user */
        $user = Auth::user();
        $user->forceFill(['last_login_at' => now()])->save();

        session()->regenerate();
        return $user;
    }

    public function logout(): void
    {
        Auth::logout();
        request()->session()->invalidate();
        request()->session()->regenerateToken();
    }

    private function throttleKey(string $email): string
    {
        return Str::lower($email).'|'.request()->ip();
    }
}
