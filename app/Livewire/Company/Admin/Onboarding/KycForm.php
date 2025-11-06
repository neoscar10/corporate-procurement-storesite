<?php

namespace App\Livewire\Company\Admin\Onboarding;

use Livewire\Component;
use Livewire\WithFileUploads;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use App\Models\Company\Company;
use App\Models\Company\CompanyKycDocument;
use App\Services\Onboarding\OnboardingProgressService;

class KycForm extends Component
{
    use WithFileUploads;

    public int $companyId;

    /** @var UploadedFile|null */
    public $pan_document = null;

    /** @var UploadedFile|null */
    public $cin_document = null;

    /** @var UploadedFile|null */
    public $gstin_document = null;

    /**
     * Prefilled state (read-only) for already uploaded docs.
     * ['pan'| 'cin' | 'gstin' => ['name'=>string|null,'url'=>string|null,'verified_at'=>string|null]]
     * url is generated via Storage::url() on the public disk.
     */
    public array $existing = [
        'pan'   => ['name' => null, 'url' => null, 'verified_at' => null],
        'cin'   => ['name' => null, 'url' => null, 'verified_at' => null],
        'gstin' => ['name' => null, 'url' => null, 'verified_at' => null],
    ];

    protected function rules(): array
    {
        return [
            'pan_document'   => ['nullable','file','mimes:pdf,jpg,jpeg,png','max:10240'],
            'cin_document'   => ['nullable','file','mimes:pdf,jpg,jpeg,png','max:10240'],
            'gstin_document' => ['nullable','file','mimes:pdf,jpg,jpeg,png','max:10240'],
        ];
    }

    public function mount(): void
    {
        $company = Company::findOrFail($this->companyId);

        $docs = CompanyKycDocument::query()
            ->where('company_id', $company->id)
            ->whereIn('document_type', ['pan','cin','gstin'])
            ->get()
            ->keyBy('document_type');

        foreach (['pan','cin','gstin'] as $t) {
            $doc = $docs->get($t);
            if (!$doc) {
                continue;
            }
            $path = (string) $doc->file_path;
            // Use the *public* disk url (works with APP_URL + /storage and your storage:link)
            $url  = Storage::disk('public')->url($path);

            $this->existing[$t] = [
                'name'        => $doc->original_name ?: basename($path),
                'url'         => $url,
                'verified_at' => optional($doc->verified_at)?->toDateTimeString(),
            ];
        }
    }

    public function save(OnboardingProgressService $progress)
    {
        $this->validate();

        $company = Company::findOrFail($this->companyId);

        // If user uploads nothing and none exist, block progression
        $uploadingNone = !($this->pan_document || $this->cin_document || $this->gstin_document);
        if ($uploadingNone) {
            $hasAny = CompanyKycDocument::where('company_id', $company->id)
                ->whereIn('document_type', ['pan','cin','gstin'])
                ->exists();
            if (! $hasAny) {
                $this->addError('pan_document', 'Please upload at least one KYC document (PAN, CIN, or GSTIN).');
                return;
            }
        }

        $this->upsertOne($company, 'pan',   $this->pan_document);
        $this->upsertOne($company, 'cin',   $this->cin_document);
        $this->upsertOne($company, 'gstin', $this->gstin_document);

        // Mark step done if at least one exists now
        $hasNow = CompanyKycDocument::where('company_id', $company->id)
            ->whereIn('document_type', ['pan','cin','gstin'])
            ->exists();

        if ($hasNow) {
            $progress->markKycDone($company);
        }

        $this->reset(['pan_document','cin_document','gstin_document']);

        return redirect()->route('company.onboarding', ['step' => 3])
            ->with('success', 'KYC documents saved.');
    }

    private function upsertOne(Company $company, string $type, ?UploadedFile $file): void
    {
        if (! $file instanceof UploadedFile) {
            return; // keep existing doc as-is
        }

        $storedPath = $file->store('company/kyc', 'public');

        CompanyKycDocument::updateOrCreate(
            ['company_id' => $company->id, 'document_type' => $type],
            [
                'file_path'     => $storedPath,
                'original_name' => $file->getClientOriginalName(),
            ]
        );

        // Refresh the visible "existing" row instantly after upload
        $this->existing[$type] = [
            'name'        => $file->getClientOriginalName(),
            'url'         => Storage::disk('public')->url($storedPath),
            'verified_at' => null,
        ];
    }

    public function render()
    {
        return view('livewire.company.admin.onboarding.kyc-form');
    }
}
