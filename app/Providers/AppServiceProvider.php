<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Models\Company\Company;
use App\Observers\CompanyObserver;
use Illuminate\Auth\Notifications\ResetPassword; // 👈 add this

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        Company::observe(CompanyObserver::class);

        // Build reset URLs like: /reset-password/{token}?email=user@example.com
        ResetPassword::createUrlUsing(function ($notifiable, string $token) {
            return route('password.reset', [
                'token' => $token,
                'email' => $notifiable->getEmailForPasswordReset(),
            ]);
        });
    }
}
