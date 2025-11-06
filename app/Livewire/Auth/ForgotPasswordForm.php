<?php

namespace App\Livewire\Auth;

use Livewire\Component;
use App\Services\Auth\PasswordResetService;

class ForgotPasswordForm extends Component
{
    public string $email = '';

    protected function rules(): array { return ['email' => ['required','email']]; }

    public function send(PasswordResetService $svc)
    {
        $this->validate();
        $svc->sendLink($this->email);
        session()->flash('success','We emailed you a password reset link.');
    }

    public function render(){
         return view('livewire.auth.forgot-password-form')->layout('layouts.auth', [
            'authTitle' => 'Forgot Password',
            'authSubtitle' => 'We’ll email you a reset linkt',
            'title' => 'Sign In • ' . config('app.name'),
        ]);  
        }
}

