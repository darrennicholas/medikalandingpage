<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('roles', function (Blueprint $table): void {
            $table->json('permissions')->nullable();
        });

        $modules = [
            'dashboard' => ['view'],
            'home_hero_banner' => ['view', 'create', 'update', 'delete', 'publish'],
            'home_coverage' => ['view', 'update', 'publish'],
            'home_cta' => ['view', 'update', 'publish'],
            'home_brands' => ['view', 'create', 'update', 'delete', 'publish'],
            'about_company' => ['view', 'update', 'publish'],
            'about_summary' => ['view', 'update', 'publish'],
            'about_mission' => ['view', 'create', 'update', 'delete', 'publish'],
            'about_certificates' => ['view', 'create', 'update', 'delete', 'publish'],
            'products_categories' => ['view', 'create', 'update', 'delete', 'publish'],
            'products_catalog' => ['view', 'create', 'update', 'delete', 'publish'],
            'documentation' => ['view', 'create', 'update', 'delete', 'publish'],
            'careers' => ['view', 'create', 'update', 'delete', 'publish'],
            'contacts' => ['view', 'update'],
            'footer' => ['view', 'update'],
            'media' => ['view', 'create', 'update', 'delete'],
            'seo' => ['view', 'update'],
            'users' => [],
            'permissions' => [],
            'activity_logs' => [],
        ];

        DB::table('roles')->where('name', 'admin')->update(['permissions' => json_encode($modules)]);
        DB::table('roles')->where('name', 'superadmin')->update(['permissions' => json_encode($modules)]);
    }

    public function down(): void
    {
        Schema::table('roles', function (Blueprint $table): void {
            $table->dropColumn('permissions');
        });
    }
};
