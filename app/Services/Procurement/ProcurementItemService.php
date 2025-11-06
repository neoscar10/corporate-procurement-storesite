<?php
namespace App\Services\Procurement;

use App\Models\Procurement\{ProcurementItem,ProcurementRequest};
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\UploadedFile;

class ProcurementItemService
{
    /** Attach files to item or request (polymorphic) */
    public function attachFiles($attachable, array $files, string $disk='public'): int
    {
        $count = 0;
        foreach ($files as $file) {
            if ($file instanceof UploadedFile) {
                $path = $file->store('procurements/'.$attachable->company_id, $disk);
                $attachable->attachments()->create([
                    'company_id' => $attachable->company_id,
                    'disk' => $disk,
                    'path' => $path,
                    'original_name' => $file->getClientOriginalName(),
                    'mime' => $file->getClientMimeType(),
                    'size_bytes' => $file->getSize(),
                    'url' => Storage::disk($disk)->url($path),
                ]);
                $count++;
            }
        }

        // update parent counter if it is a request
        if ($attachable instanceof ProcurementRequest) {
            $attachable->increment('attachments_count', $count);
        }

        return $count;
    }

    public function deleteItem(ProcurementItem $item): void
    {
        $item->loadMissing(['attachments', 'productSpec', 'serviceSpec']);

        // Remove physical files + attachment rows (table has no "id" PK)
        foreach ($item->attachments as $att) {
            $disk = $att->disk ?: 'public';
            $path = $att->path;

            // delete the file if present
            if ($path && Storage::disk($disk)->exists($path)) {
                Storage::disk($disk)->delete($path);
            }

            // delete the DB row via relation (scoped by attachable already)
            $item->attachments()
                ->where('disk', $disk)
                ->where('path', $path)
                ->limit(1)
                ->delete();
        }

        // Remove specs (if any)
        if ($item->productSpec) {
            $item->productSpec->delete();
        }
        if ($item->serviceSpec) {
            $item->serviceSpec->delete();
        }

        // Finally, delete the item
        $item->delete();
    }
}
