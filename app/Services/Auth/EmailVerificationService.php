<?php

namespace App\Services\Auth;

use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\Notification;
use Illuminate\Notifications\Messages\MailMessage;
use App\Models\User;

class EmailVerificationService
{
    public function send(User $user): void
    {
        // If you later implement MustVerifyEmail on User, use built-in notification.
        $url = URL::temporarySignedRoute(
            'verification.verify',
            now()->addMinutes(60),
            ['id' => $user->id, 'hash' => sha1($user->email)]
        );

        Notification::route('mail', $user->email)->notify(
            new class($url) extends \Illuminate\Notifications\Notification {
                public function __construct(public string $url) {}
                public function via($notifiable) { return ['mail']; }
                public function toMail($notifiable) {
                    return (new MailMessage)
                        ->subject('Verify your email')
                        ->line('Click the button below to verify your email address.')
                        ->action('Verify Email', $this->url)
                        ->line('This link expires in 60 minutes.');
                }
            }
        );
    }

    public function markVerified(User $user): void
    {
        if (is_null($user->email_verified_at)) {
            $user->forceFill(['email_verified_at' => now()])->save();
        }
    }
}
