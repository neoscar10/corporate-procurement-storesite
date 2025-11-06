<?php

namespace App\Services\Auth;

use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Hash;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Validation\ValidationException;
use App\Models\User;
use Illuminate\Support\Str;

class PasswordResetService
{
    public function sendLink(string $email): void
    {
        $status = Password::broker()->sendResetLink(['email' => $email]);

        if ($status !== Password::RESET_LINK_SENT) {
            throw ValidationException::withMessages([
                'email' => __($status),
            ]);
        }
    }

    public function reset(string $email, string $token, string $password): void
    {
        $status = Password::broker()->reset(
            compact('email', 'token', 'password'),
            function (User $user) use ($password) {
                $user->forceFill([
                    'password' => Hash::make($password),
                    'last_password_change_at' => now(),
                    'remember_token' => Str::random(60),
                ])->save();

                event(new PasswordReset($user));
            }
        );

        if ($status !== Password::PASSWORD_RESET) {
            throw ValidationException::withMessages([
                'email' => __($status),
            ]);
        }
    }
}
