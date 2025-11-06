<?php

namespace App\Livewire\Auth;

use Livewire\Component;
use App\Services\Auth\WebAuthService;
use App\Models\Company\CompanyMember;

class LoginForm extends Component
{
    public string $email = '';
    public string $password = '';
    public bool $remember = false;

    protected function rules(): array
    {
        return [
            'email' => ['required','email'],
            'password' => ['required','string'],
        ];
    }

    public function submit(WebAuthService $auth)
    {
        $data = $this->validate();
        $user = $auth->login($data['email'], $data['password'], $this->remember);

        // 1) Super admin takes precedence
        if (!empty($user->is_admin) && (int)$user->is_admin === 1) {
            return redirect()->intended(route('admin.dashboard'));
        }

        // 2) Company membership (latest active)
        $membership = CompanyMember::with('company')
            ->where('user_id', $user->id)
            ->where('is_active', true)
            ->latest('id')
            ->first();

        if ($membership && $membership->company) {
            $status = $membership->company->status;

            // Send both pending and rejected to onboarding
            if (in_array($status, ['pending','rejected'], true)) {
                return redirect()->route('company.onboarding');
            }

            // Route by company role (default to user)
            $role = $membership->role ?? 'user';
            if ($role === 'company_admin') {
                return redirect()->intended(route('company.admin.dashboard'));
            }
            return redirect()->intended(route('company.user.dashboard'));
        }

        // 3) Fallback
        return redirect()->intended(route('company.admin.dashboard'));
    }

    public function render()
    {
        return view('livewire.auth.login-form')->layout('layouts.auth', [
            'authTitle' => 'Sign In',
            'authSubtitle' => 'Use your credentials to access your account',
            'title' => 'Sign In • ' . config('app.name'),
        ]);
    }
}
