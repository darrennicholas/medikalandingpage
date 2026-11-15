<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([RoleSeeder::class, CmsUserSeeder::class, CmsProductSeeder::class, LegacyProductCategorySeeder::class]);
    }
}
