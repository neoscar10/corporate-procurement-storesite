<?php
namespace App\Services\Procurement;

use App\Models\Procurement\{ProcurementItem,ProcurementRequest};
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use App\Services\Procurement\ProcurementRequestService;

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
        DB::transaction(function () use ($item) {
            $item->loadMissing(['attachments', 'productSpec', 'serviceSpec', 'request']);

            // delete files + rows
            foreach ($item->attachments as $att) {
                $disk = $att->disk ?: 'public';
                $path = $att->path;
                if ($path && Storage::disk($disk)->exists($path)) {
                    Storage::disk($disk)->delete($path);
                }
                $item->attachments()
                    ->where('disk', $disk)
                    ->where('path', $path)
                    ->limit(1)
                    ->delete();
            }

            if ($item->productSpec) $item->productSpec->delete();
            if ($item->serviceSpec) $item->serviceSpec->delete();

            $req = $item->request; // keep parent before delete
            $item->delete();

            if ($req) {
                // (optional) keep items_count truthful if you maintain it
                if (isset($req->items_count) && $req->items_count > 0) {
                    $req->decrement('items_count');
                }
                app(ProcurementRequestService::class)->invalidateApprovals($req, 'item_deleted');
            }
        });
    }
}
