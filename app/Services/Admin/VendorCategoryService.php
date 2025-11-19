<?php

namespace App\Services\Admin;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Str;
use App\Models\Vendor\VendorCategory;
use Illuminate\Database\QueryException;


class VendorCategoryService
{
    public function paginate(array $filters, int $perPage = 15): LengthAwarePaginator
    {
        $q = VendorCategory::query();

        // Filter by kind (default: product)
        $kind = $filters['kind'] ?? 'product';
        $q->where('kind', in_array($kind, ['product','service'], true) ? $kind : 'product');

        // Search by name/slug
        if (!empty($filters['search'])) {
            $term = '%'.trim($filters['search']).'%';
            $q->where(function($qq) use ($term) {
                $qq->where('name','like',$term)
                   ->orWhere('slug','like',$term);
            });
        }

        // Active filter ('' | '1' | '0')
        if (array_key_exists('active', $filters) && $filters['active'] !== '') {
            $q->where('is_active', $filters['active'] === '1');
        }

        // Order: display_order ASC, name ASC
        $q->orderBy('display_order','asc')->orderBy('name','asc');

        return $q->paginate($perPage);
    }

    public function counts(): array
    {
        return [
            'product' => VendorCategory::where('kind','product')->count(),
            'service' => VendorCategory::where('kind','service')->count(),
        ];
    }

   public function upsert(array $payload, ?int $id = null): VendorCategory
{
    // single-layer: NO parent_id
    $kind = in_array(($payload['kind'] ?? 'product'), ['product','service'], true)
        ? $payload['kind'] : 'product';

    $name = trim($payload['name'] ?? '');
    $slugBase = trim($payload['slug'] ?? '');
    $slugBase = \Illuminate\Support\Str::slug($slugBase !== '' ? $slugBase : $name);
    if ($slugBase === '') $slugBase = 'category';

    $data = [
        'kind'          => $kind,
        'name'          => $name,
        'slug'          => $this->uniqueSlug($kind, $slugBase, $id), // ensure unique before save
        'description'   => $payload['description'] ?? null,
        'is_active'     => isset($payload['is_active']) ? (bool)$payload['is_active'] : true,
        'display_order' => (int)($payload['display_order'] ?? 0),
    ];

    try {
        if ($id) {
            $cat = VendorCategory::findOrFail($id);
            $cat->fill($data)->save();
            return $cat->fresh();
        }

        return VendorCategory::create($data);

    } catch (QueryException $e) {
        // Handle rare race where another row grabbed the same slug
        if ((int)($e->errorInfo[1] ?? 0) === 1062) {
            $data['slug'] = $this->uniqueSlug($kind, $slugBase.'-'.\Illuminate\Support\Str::random(4), $id);
            if ($id) {
                $cat = VendorCategory::findOrFail($id);
                $cat->fill($data)->save();
                return $cat->fresh();
            }
            return VendorCategory::create($data);
        }
        throw $e;
    }
}


    public function toggleActive(int $id): VendorCategory
    {
        $cat = VendorCategory::findOrFail($id);
        $cat->is_active = ! $cat->is_active;
        $cat->save();
        return $cat->fresh();
    }

    public function delete(int $id): void
    {
        // single-layer: no children constraint to check
        $cat = VendorCategory::findOrFail($id);
        $cat->delete();
    }

    public function move(int $id, string $dir): VendorCategory
    {
        $cat = VendorCategory::findOrFail($id);
        $delta = $dir === 'up' ? -1 : 1;
        $cat->display_order = max(0, (int)$cat->display_order + $delta);
        $cat->save();
        return $cat->fresh();
    }
    private function uniqueSlug(string $kind, string $base, ?int $ignoreId = null): string
{
    $base = $base !== '' ? \Illuminate\Support\Str::slug($base) : 'category';
    $candidate = $base;
    $suffix = 0;
    $maxLen = 140;

    $exists = function (string $slug) use ($kind, $ignoreId): bool {
        return VendorCategory::where('kind', $kind)
            ->where('slug', $slug)
            ->when($ignoreId, fn($q) => $q->where('id', '!=', $ignoreId))
            ->exists();
    };

    while ($exists($candidate)) {
        $suffix++;
        $suffixStr = (string)$suffix;
        $trimBase = \Illuminate\Support\Str::limit($base, $maxLen - 1 - strlen($suffixStr), '');
        $candidate = $trimBase.'-'.$suffixStr;

        // safety valve for extreme collisions
        if ($suffix > 1000) {
            $candidate = \Illuminate\Support\Str::limit($base, $maxLen - 7, '')
                        .'-'.\Illuminate\Support\Str::random(6);
            if (! $exists($candidate)) break;
        }
    }
    return $candidate;
}
}
