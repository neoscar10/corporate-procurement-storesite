<?php

namespace App\Mail;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
// If you want to queue emails, also: use Illuminate\Contracts\Queue\ShouldQueue;

class UserCredentialsMailable extends Mailable // implements ShouldQueue
{
    use Queueable, SerializesModels;

    public function __construct(
        public User $user,
        public string $password,
        public array $permissions = [],
        public ?string $loginUrl = null,
    ) {
        $this->loginUrl = $loginUrl ?: route('login');
    }

    public function build()
    {
        return $this->subject('Your account credentials')
            ->markdown('emails.user-credentials', [
                'user'        => $this->user,
                'password'    => $this->password,
                'permissions' => $this->permissions,
                'loginUrl'    => $this->loginUrl,
                'appName'     => config('app.name'),
            ]);
    }
}
