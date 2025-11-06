<?php

namespace App\Services\Support;

use Illuminate\Support\Facades\Notification;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Support\Facades\Mail;

use App\Models\User;
use App\Mail\UserCredentialsMailable;

use App\Models\Procurement\ProcurementRequest;
use App\Notifications\Procurement\{
    NewApprovalRequest,
    ApprovedNotification,
    RejectedNotification,
    PublishedNotification
};

class NotificationService
{
    /**
     * OTP - email (default) or SMS (placeholder hook).
     */
    public function sendOtp(array $to, string $code, string $channel = 'email'): void
    {
        if ($channel === 'sms' && !empty($to['phone'])) {
            // Hook your SMS provider here
            logger()->info('SMS OTP', ['to' => $to['phone'], 'code' => $code]);
            return;
        }

        if (!empty($to['email'])) {
            Notification::route('mail', $to['email'])
                ->notify(new class($code) extends \Illuminate\Notifications\Notification {
                    public function __construct(public string $code) {}
                    public function via($notifiable) { return ['mail']; }
                    public function toMail($notifiable) {
                        return (new MailMessage)
                            ->subject('Your verification code')
                            ->line('Use this code to verify your account:')
                            ->line($this->code)
                            ->line('This code expires in 10 minutes.');
                    }
                });
        }
    }

    /**
     * Credentials (simple). For production, consider magic-link / set-password flow.
     */
    public function sendCredentials(User $user, string $password): void
    {
        $user->notify(new class($password) extends \Illuminate\Notifications\Notification {
            public function __construct(public string $password) {}
            public function via($notifiable) { return ['mail']; }
            public function toMail($notifiable) {
                return (new MailMessage)
                    ->subject('Your account is ready')
                    ->greeting('Hello '.$notifiable->name)
                    ->line('Your account has been created.')
                    ->line('Email: '.$notifiable->email)
                    ->line('Temporary Password: '.$this->password)
                    ->action('Login', url('/login'))
                    ->line('Please change your password after login.');
            }
        });
    }

    /**
     * Credentials with permission breakdown (mailable template).
     */
    public function sendCredentialsWithPermissions(User $user, string $password, array $permissionLabels = []): void
    {
        Mail::to($user->email)->send(
            new UserCredentialsMailable($user, $password, $permissionLabels)
        );
    }

    // =========================
    // Procurement notifications
    // =========================

    /**
     * Notify approvers that their approval is required.
     *
     * @param ProcurementRequest $req
     * @param int[] $approverIds
     */
    public function procurementApprovalRequested(ProcurementRequest $req, array $approverIds): void
    {
        $users = User::whereIn('id', $approverIds)->get();
        if ($users->isNotEmpty()) {
            Notification::send($users, new NewApprovalRequest($req));
        }
    }

    /**
     * Notify interested parties that a request is approved.
     */
    public function procurementApproved(ProcurementRequest $req): void
    {
        $users = $this->interestedUsers($req);
        if ($users->isNotEmpty()) {
            Notification::send($users, new ApprovedNotification($req));
        }
    }

    /**
     * Notify interested parties that a request was rejected.
     */
    public function procurementRejected(ProcurementRequest $req, int $byUserId): void
    {
        $users = $this->interestedUsers($req);
        if ($users->isNotEmpty()) {
            Notification::send($users, new RejectedNotification($req, $byUserId));
        }
    }

    /**
     * Notify interested parties that a request was published.
     */
    public function procurementPublished(ProcurementRequest $req): void
    {
        $users = $this->interestedUsers($req);
        if ($users->isNotEmpty()) {
            Notification::send($users, new PublishedNotification($req));
        }
    }

    /**
     * Creator + watchers + company admins (for the same company).
     */
    protected function interestedUsers(ProcurementRequest $req)
    {
        $creatorId = (int) $req->created_by;

        $watcherIds = method_exists($req, 'watchers')
            ? $req->watchers()->pluck('user_id')->map(fn($i)=>(int)$i)->all()
            : [];

        $companyAdminIds = User::whereHas('companyMembers', function ($q) use ($req) {
                $q->where('company_id', $req->company_id)
                  ->where('role_label', 'CompanyAdmin');
            })
            ->pluck('id')
            ->map(fn($i)=>(int)$i)
            ->all();

        $ids = array_values(array_unique(array_merge([$creatorId], $watcherIds, $companyAdminIds)));
        return User::whereIn('id', $ids)->get();
    }
}
