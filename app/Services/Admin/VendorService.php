<?php

namespace App\Services\Admin;

use App\Models\User;
use App\Models\Vendor\Vendor;
use App\Models\Vendor\VendorCategory;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use App\Mail\Vendor\WelcomeVendorMail;

class VendorService
{
    public function paginate(array $filters, int $perPage = 15): LengthAwarePaginator
    {
        $q = Vendor::query()
            ->with(['categories:id,name,kind'])
            ->with('user:id,is_vendor');

        if (!empty($filters['search'])) {
            $s = '%'.trim($filters['search']).'%';
            $q->where(function($qq) use ($s) {
                $qq->where('name','like',$s)
                   ->orWhere('email','like',$s)
                   ->orWhere('phone','like',$s)
                   ->orWhere('company_name','like',$s);
            });
        }

        if (isset($filters['active']) && $filters['active'] !== '') {
            $q->where('is_active', $filters['active'] === '1');
        }

        if (($filters['provides'] ?? '') === 'products') {
            $q->where('provides_products', true);
        } elseif (($filters['provides'] ?? '') === 'services') {
            $q->where('provides_services', true);
        }

        $q->orderByDesc('id');
        return $q->paginate($perPage);
    }

    public function categoryOptions(): array
    {
        return [
            'product' => VendorCategory::where('kind','product')->orderBy('name')->get(['id','name']),
            'service' => VendorCategory::where('kind','service')->orderBy('name')->get(['id','name']),
        ];
    }

    /**
     * Create or update a vendor.
     * On create:
     *  - creates/updates a User (marks is_vendor) + generates temp password if new
     *  - creates Vendor row
     *  - syncs categories (product/service sets)
     *  - emails temp password to the vendor
     */
    public function upsert(array $data, ?int $id = null): Vendor
    {
        // Normalize booleans
        $providesProducts = (bool)($data['provides_products'] ?? false);
        $providesServices = (bool)($data['provides_services'] ?? false);

        $productCategoryIds = array_map('intval', $data['product_category_ids'] ?? []);
        $serviceCategoryIds = array_map('intval', $data['service_category_ids'] ?? []);

        // Filter category arrays to ensure proper kind
        if (! empty($productCategoryIds)) {
            $productCategoryIds = VendorCategory::whereIn('id',$productCategoryIds)->where('kind','product')->pluck('id')->all();
        }
        if (! empty($serviceCategoryIds)) {
            $serviceCategoryIds = VendorCategory::whereIn('id',$serviceCategoryIds)->where('kind','service')->pluck('id')->all();
        }

        if ($id) {
            $vendor = Vendor::findOrFail($id);

            // Email is not changed during update (safer)
            $vendor->fill([
                'name'               => trim($data['name'] ?? $vendor->name),
                'phone'              => $data['phone'] ?? $vendor->phone,
                'company_name'       => $data['company_name'] ?? $vendor->company_name,
                'provides_products'  => $providesProducts,
                'provides_services'  => $providesServices,
                'is_active'          => isset($data['is_active']) ? (bool)$data['is_active'] : $vendor->is_active,
            ])->save();

            // Sync categories based on toggles
            $all = [];
            if ($providesProducts)  $all = array_merge($all, $productCategoryIds);
            if ($providesServices)  $all = array_merge($all, $serviceCategoryIds);
            $vendor->categories()->sync(array_unique($all));

            // Optionally update attached user’s name/phone
            if ($vendor->user) {
                if ($vendor->name && ! $vendor->user->name) {
                    $vendor->user->name = $vendor->name;
                }
                if (! is_null($vendor->user->is_vendor) && $vendor->user->is_vendor !== true) {
                    $vendor->user->is_vendor = true;
                }
                $vendor->user->save();
            }

            return $vendor->fresh(['categories','user']);
        }

        // CREATE
        $email = strtolower(trim($data['email'] ?? ''));
        $existingUser = $email ? User::where('email', $email)->first() : null;
        $plainPassword = null;

        if ($existingUser) {
            // promote to vendor if not already
            if ($existingUser->is_vendor !== true) {
                $existingUser->is_vendor = true;
                $existingUser->save();
            }
            $user = $existingUser;
        } else {
            $plainPassword = Str::random(10);
            $user = User::create([
                'name'      => trim($data['name'] ?? ''),
                'email'     => $email,
                'password'  => Hash::make($plainPassword),
                'is_vendor' => true,
            ]);
        }

        $vendor = Vendor::create([
            'user_id'            => $user->id,
            'name'               => trim($data['name'] ?? ''),
            'email'              => $email,
            'phone'              => $data['phone'] ?? null,
            'company_name'       => $data['company_name'] ?? null,
            'provides_products'  => $providesProducts,
            'provides_services'  => $providesServices,
            'is_active'          => isset($data['is_active']) ? (bool)$data['is_active'] : true,
        ]);

        $all = [];
        if ($providesProducts)  $all = array_merge($all, $productCategoryIds);
        if ($providesServices)  $all = array_merge($all, $serviceCategoryIds);
        $vendor->categories()->sync(array_unique($all));

        // Email temp credentials if we created a new user
        if ($plainPassword) {
            try {
                Mail::to($user->email)->send(new WelcomeVendorMail($user, $plainPassword));
            } catch (\Throwable $e) {
                // swallow email errors; creation should still succeed
                report($e);
            }
        }

        return $vendor->fresh(['categories','user']);
    }

    public function toggleActive(int $id): Vendor
    {
        $v = Vendor::findOrFail($id);
        $v->is_active = ! $v->is_active;
        $v->save();
        return $v->fresh();
    }

    public function delete(int $id): void
    {
        $v = Vendor::findOrFail($id);
        $v->categories()->detach();
        $v->delete();
    }
}
