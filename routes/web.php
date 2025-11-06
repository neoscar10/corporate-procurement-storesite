<?php

use App\Livewire\Home\Index;
use Illuminate\Support\Facades\Route;

use App\Livewire\Auth\Register\WizardPage;
use App\Livewire\Auth\LoginForm;
use App\Livewire\Auth\ForgotPasswordForm;
use App\Livewire\Auth\ResetPasswordForm;
use App\Livewire\Company\Admin\Onboarding;
use App\Livewire\Company\Dashboards\AdminDashboard;
use App\Services\Auth\WebAuthService;
use App\Livewire\Admin\Companies\Requests\Index as CompanyRequestsIndex;
use App\Livewire\Admin\Companies\Requests\Show as CompanyRequestsShow;
use App\Livewire\Admin\Dashbaord\AdminDashboard as DashbaordAdminDashboard;
use App\Livewire\Company\Procurement\Index as CompanyProcIndex;
use App\Livewire\Company\Procurement\Show as CompanyProcureShow;

Route::middleware('guest')->get('/register', WizardPage::class)->name('register');

// Guest-only auth pages
Route::middleware('guest')->group(function () {
    Route::get('/', Index::class)->name('home'); //Home page

    Route::get('/login', LoginForm::class)->name('login');
    Route::get('/forgot-password', ForgotPasswordForm::class)->name('password.request');
    Route::get('/reset-password/{token}', ResetPasswordForm::class)->name('password.reset');
});

// Logout (POST) — session guard
Route::post('/logout', function (WebAuthService $auth) {
    $auth->logout();
    return redirect()->route('login');
})->middleware('auth')->name('logout');

// Company admin routes.......no middleware yet
    Route::middleware('auth')->group(function(){
    Route::get('admin/dashboard', AdminDashboard::class)->name('company.admin.dashboard');
    Route::get('admin/onboarding', Onboarding::class)->name('company.onboarding');
    
    Route::get('company/procurements', CompanyProcIndex::class)->name('company.procurements');
    Route::get('/procure/requests/{requestId}', CompanyProcureShow::class)
        ->name('company.procure.requests.show');
});





// Super admon routes, no middleware yet
Route::middleware(['auth'])->prefix('admin/companies')->name('admin.company.')->group(function () {
    Route::get('/requests', CompanyRequestsIndex::class)->name('requests.index');
    Route::get('/requests/{company}', CompanyRequestsShow::class)->name('requests.show');
});

Route::get('/super-admin/dashboard', \App\Livewire\Admin\Dashbaord\AdminDashboard::class)->name('admin.dashboard');

