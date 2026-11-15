<?php

namespace Database\Seeders;

use App\Models\Role;
use Illuminate\Database\Seeder;

class RoleSeeder extends Seeder
{
    public function run(): void
    {
        Role::query()->updateOrCreate(['name' => 'superadmin'], ['description' => 'Akses penuh CMS']);
        Role::query()->updateOrCreate(['name' => 'admin'], ['description' => 'Pengelola konten Digital Marketing']);
    }
}
