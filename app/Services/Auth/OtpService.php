<?php

namespace App\Services\Auth;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;
use App\Services\Support\NotificationService;

class OtpService
{
    public function __construct(protected NotificationService $notify) {}

    public function issue(array $to, string $channel = 'email'): string
    {
        $otpId = (string) Str::uuid();
        $code  = (string) random_int(100000, 999999);

        Cache::put($this->key($otpId), [
            'code'  => $code,
            'email' => $to['email'] ?? null,
            'phone' => $to['phone'] ?? null,
            'tries' => 0,
        ], now()->addMinutes(10));

        $this->notify->sendOtp($to, $code, $channel);
        return $otpId;
    }

    public function verify(string $otpId, string $code, array $target): void
    {
        $key = $this->key($otpId);
        $data = Cache::get($key);
        if (!$data) {
            throw new \RuntimeException('OTP expired.');
        }
        if (!self::matchesTarget($data, $target)) {
            throw new \RuntimeException('OTP target mismatch.');
        }
        $data['tries']++;
        if ($data['tries'] > 5) {
            Cache::forget($key);
            throw new \RuntimeException('OTP locked.');
        }
        if ($data['code'] !== $code) {
            Cache::put($key, $data, now()->addMinutes(5));
            throw new \RuntimeException('Invalid OTP.');
        }
        Cache::forget($key);
    }

    private function key(string $otpId): string
    {
        return "otp:{$otpId}";
    }

    private static function matchesTarget(array $data, array $target): bool
    {
        $okEmail = empty($target['email']) || $data['email'] === $target['email'];
        $okPhone = empty($target['phone']) || $data['phone'] === $target['phone'];
        return $okEmail && $okPhone;
    }
}
