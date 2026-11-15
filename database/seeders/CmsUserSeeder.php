<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;

class CmsUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $superadminRole = Role::query()->where('name', 'superadmin')->firstOrFail();
        $adminRole = Role::query()->where('name', 'admin')->firstOrFail();

        $superadmin = User::query()->firstOrCreate(
            ['email' => 'superadmin@medikalandingpage.test'],
            [
                'name' => 'Superadmin',
                'password_hash' => 'developer',
                'role_id' => $superadminRole->id,
                'status' => 'active',
                'must_change_password' => false,
            ],
        );
        $superadmin->forceFill(['must_change_password' => false])->save();

        $admin = User::query()->firstOrCreate(
            ['email' => 'admin.digitalmarketing@medikalandingpage.test'],
            [
                'name' => 'Admin Digital Marketing',
                'password_hash' => 'developer',
                'role_id' => $adminRole->id,
                'status' => 'active',
                'must_change_password' => false,
            ],
        );
        $admin->forceFill(['must_change_password' => false])->save();
    }
}
