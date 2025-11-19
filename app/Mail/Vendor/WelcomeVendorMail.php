<?php

namespace App\Mail\Vendor;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class WelcomeVendorMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public User $user, public string $plainPassword) {}

    public function build()
    {
        return $this->subject('Your Vendor Account')
            ->view('emails.vendor.welcome');
    }
}
