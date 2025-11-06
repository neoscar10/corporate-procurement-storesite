<?php

namespace App\Services\Kyc;

use App\Models\Company\Company;
use App\Models\Company\CompanyKycDocument;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class CompanyKycService
{
    /**
     * Save many docs (replace if same document_type already exists).
     * $docs = [['document_type'=>'pan','file'=>UploadedFile|path,'original_name'=>...], ...]
     */
    public function storeMany(Company $company, array $docs): void
    {
        foreach ($docs as $doc) {
            if (! isset($doc['document_type'], $doc['file'])) {
                continue;
            }
            $this->replaceOne($company, (string) $doc['document_type'], $doc['file'], $doc['original_name'] ?? null);
        }
    }

    /**
     * Replace a single document by type (delete file + update record).
     */
    public function replaceOne(Company $company, string $documentType, UploadedFile|string $file, ?string $originalName = null): void
    {
        $path = $this->storeFile($file);

        /** @var CompanyKycDocument|null $existing */
        $existing = CompanyKycDocument::where('company_id', $company->id)
            ->where('document_type', $documentType)
            ->first();

        if ($existing) {
            // delete old file if it was on public disk
            if (!empty($existing->file_path) && Storage::disk('public')->exists($existing->file_path)) {
                Storage::disk('public')->delete($existing->file_path);
            }
            $existing->forceFill([
                'file_path'     => $path,
                'original_name' => $originalName,
            ])->save();
            return;
        }

        CompanyKycDocument::create([
            'company_id'    => $company->id,
            'document_type' => $documentType,
            'file_path'     => $path,
            'original_name' => $originalName,
        ]);
    }

    private function storeFile(UploadedFile|string $file): string
    {
        if ($file instanceof UploadedFile) {
            // Always use public disk so Storage::url(...) works
            return $file->store('company/kyc', 'public');
        }
        return (string) $file;
    }
}
