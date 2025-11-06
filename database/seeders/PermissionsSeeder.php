<?php
namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Permission;

class PermissionsSeeder extends Seeder
{
    public function run(): void
    {
        $map = [
            'see_procurements'               => 'See procurements',
            'create_procurement'             => 'Create procurement',
            'approve_procurement'            => 'Approve procurement',
            'see_quotations'                 => 'See quotations',
            'shortlist_or_schedule_meeting'  => 'Shortlist quotation / Schedule meeting',
            'sign_contract'                  => 'Sign contract',
            'accept_delivery'                => 'Accept delivery',
            'manage_users'                   => 'Manage users',
        ];

        foreach ($map as $name => $label) {
            Permission::query()->updateOrCreate(['name' => $name], ['label' => $label]);
        }
    }
}
