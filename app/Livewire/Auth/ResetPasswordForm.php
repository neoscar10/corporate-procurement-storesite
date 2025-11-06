<?php
// app/Livewire/Auth/ResetPasswordForm.php
namespace App\Livewire\Auth;

use Livewire\Component;
use App\Services\Auth\PasswordResetService;

class ResetPasswordForm extends Component
{
    public string $email = '';
    public string $token = '';
    public string $password = '';
    public string $password_confirmation = '';

   
    public function mount(string $token): void
    {
        $this->token = $token;
        $this->email = (string) request()->query('email', '');
    }

    protected function rules(): array
    {
        return [
            'email' => ['required','email'],
            'token' => ['required','string'],
            'password' => ['required','string','min:8','confirmed'],
        ];
    }

    public function submit(PasswordResetService $svc)
    {
        $this->validate();
        $svc->reset($this->email, $this->token, $this->password);
        session()->flash('success', 'Password updated. You can now log in.');
        return redirect()->route('login');
    }

    public function render(){ 
        return view('livewire.auth.reset-password-form')->layout('layouts.auth', [
            'authTitle' => 'Reset Password',
            'authSubtitle' => 'Choose a new secure password',
            'title' => 'Sign In • ' . config('app.name'),
        ]);  
    }
}
