<?php

use App\Http\Controllers\Admin\PermissionMatrixController;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        DB::table('roles')->where('name', 'admin')->update(['permissions' => json_encode(PermissionMatrixController::defaultPermissions(), JSON_THROW_ON_ERROR)]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Keep any permissions configured after this migration.
    }
};
