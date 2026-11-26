<?php

namespace Database\Seeders;

use App\Http\Controllers\Admin\PermissionMatrixController;
use App\Models\Role;
use Illuminate\Database\Seeder;

class RoleSeeder extends Seeder
{
    public function run(): void
    {
        Role::query()->updateOrCreate(['name' => 'superadmin'], ['description' => 'Akses penuh CMS']);
        Role::query()->firstOrCreate(['name' => 'admin'], ['description' => 'Pengelola konten Digital Marketing', 'permissions' => PermissionMatrixController::defaultPermissions()]);
    }
}
