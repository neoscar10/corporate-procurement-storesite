<?php

namespace App\Services\Onboarding;

use Illuminate\Support\Facades\DB;
use App\Services\Company\CompanyService;
use App\Services\Company\CompanyAddressService;
use App\Services\Company\CompanyContactService;
use App\Services\Company\CompanyRepresentativeService;
use App\Services\Auth\OtpService;
use App\Services\Auth\UserProvisioningService;
use App\Services\Kyc\CompanyKycService;
use App\Services\Procurement\ProcurementPreferenceService;
use App\Services\Finance\CompanyBankAccountService;
use App\Services\Company\CompanyApprovalService;
use App\Models\Company\Company;

class CompanyOnboardingService
{
    public function __construct(
        protected CompanyService $company,
        protected CompanyAddressService $addresses,
        protected CompanyContactService $contacts,
        protected CompanyRepresentativeService $rep,
        protected OtpService $otp,
        protected UserProvisioningService $users,
        protected CompanyKycService $kyc,
        protected ProcurementPreferenceService $prefs,
        protected CompanyBankAccountService $bank,
        protected CompanyApprovalService $approval,
    ) {}

    // Step 1: basic company info (status => pending)
    public function upsertBasicInfo(array $data): Company
    {
        return DB::transaction(fn () => $this->company->upsert($data));
    }

    // Step 2: registered/corporate/billing address
    public function upsertAddresses(Company $company, array $addresses): Company
    {
        return DB::transaction(function () use ($company, $addresses) {
            $this->addresses->syncTyped($company, $addresses);
            return $company->fresh('addresses');
        });
    }

    // Step 3: contact info
    public function upsertContact(Company $company, array $payload): Company
    {
        return DB::transaction(function () use ($company, $payload) {
            $this->contacts->upsert($company, $payload);
            return $company->fresh('contact');
        });
    }

    // Step 4: authorized representative → generate OTP and send
    public function registerAuthorizedUserAndSendOtp(Company $company, array $repData, string $channel = 'email'): array
    {
        return DB::transaction(function () use ($company, $repData, $channel) {
            $rep = !empty($repData)
                ? $this->rep->upsert($company, $repData)
                : $company->representative()->firstOrFail(); // don't clobber

            if ($channel === 'email' && empty($rep->email)) {
                throw new \RuntimeException('Authorized user email is missing.');
            }
            if ($channel === 'sms' && empty($rep->mobile)) {
                throw new \RuntimeException('Authorized user mobile is missing.');
            }

            $otpId = $this->otp->issue(
                to: ['email' => $rep->email, 'phone' => $rep->mobile],
                channel: $channel
            );

            return ['representative' => $rep, 'otp_id' => $otpId];
        });
    }


    // Step 5: verify OTP → create first user, assign company_admin, email creds
    public function verifyOtpAndCreateFirstUser(Company $company, string $otpId, string $code): array
    {
        return DB::transaction(function () use ($company, $otpId, $code) {
            $target = [
                'email' => optional($company->representative)->email,
                'phone' => optional($company->representative)->mobile,
            ];
            $this->otp->verify($otpId, $code, $target);

            $result = $this->users->provisionFirstCompanyUser($company);
            return ['user' => $result['user'], 'password' => $result['password']];
        });
    }

    // After login: KYC uploads
    public function addKycDocuments(Company $company, array $docs): Company
    {
        return DB::transaction(function () use ($company, $docs) {
            $this->kyc->storeMany($company, $docs);
            return $company->fresh('kycDocuments');
        });
    }

    // After login: procurement preferences
    public function setPreferences(Company $company, array $prefs): Company
    {
        return DB::transaction(function () use ($company, $prefs) {
            $this->prefs->upsert($company, $prefs);
            return $company->fresh('preference');
        });
    }

    // After login: billing/bank
    public function addOrSetBank(Company $company, array $bank): Company
    {
        return DB::transaction(function () use ($company, $bank) {
            $this->bank->addOrUpdate($company, $bank);
            return $company->fresh('bankAccounts');
        });
    }

    // Super admin approval
    public function approveOrReject(Company $company, string $status, ?string $reason = null): Company
    {
        return DB::transaction(fn () => $this->approval->setStatus($company, $status, $reason));
    }
}
