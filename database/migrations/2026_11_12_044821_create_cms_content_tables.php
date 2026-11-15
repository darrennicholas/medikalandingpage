<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('roles', function (Blueprint $table) {
            $table->id();
            $table->string('name', 30)->unique();
            $table->string('description', 150);
        });

        DB::table('roles')->insert([
            ['name' => 'superadmin', 'description' => 'Akses penuh CMS'],
            ['name' => 'admin', 'description' => 'Pengelola konten Digital Marketing'],
        ]);

        Schema::table('users', function (Blueprint $table): void {
            $table->string('password_hash')->nullable();
            $table->foreignId('role_id')->nullable()->constrained('roles')->restrictOnDelete();
            $table->string('status', 20)->default('inactive');
            $table->unsignedInteger('failed_login_count')->default(0);
            $table->timestamp('locked_until')->nullable();
            $table->timestamp('last_login_at')->nullable();
            $table->boolean('must_change_password')->default(false);
            $table->softDeletes();
        });

        DB::table('users')
            ->whereNull('password_hash')
            ->update(['password_hash' => DB::raw('password')]);

        $adminRoleId = DB::table('roles')->where('name', 'admin')->value('id');
        DB::table('users')
            ->whereNull('role_id')
            ->update(['role_id' => $adminRoleId, 'status' => 'inactive']);

        Schema::table('users', function (Blueprint $table): void {
            $table->dropColumn('password');
        });

        Schema::create('media', function (Blueprint $table): void {
            $table->id();
            $table->string('file_name')->unique();
            $table->string('original_name');
            $table->string('path', 500);
            $table->string('mime_type', 50);
            $table->unsignedBigInteger('size_bytes');
            $table->unsignedInteger('width')->nullable();
            $table->unsignedInteger('height')->nullable();
            $table->string('alt_text_id', 200)->nullable();
            $table->string('alt_text_en', 200)->nullable();
            $table->foreignId('uploaded_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('activity_logs', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('action', 30);
            $table->string('entity_type', 50)->nullable();
            $table->unsignedBigInteger('entity_id')->nullable();
            $table->json('old_values')->nullable();
            $table->json('new_values')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->string('user_agent', 300)->nullable();
            $table->timestamp('created_at')->useCurrent();
            $table->index(['entity_type', 'entity_id']);
            $table->index(['user_id', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('activity_logs');
        Schema::dropIfExists('media');
        Schema::table('users', function (Blueprint $table): void {
            $table->dropConstrainedForeignId('role_id');
            $table->string('password')->nullable();
        });
        DB::table('users')
            ->whereNull('password')
            ->update(['password' => DB::raw('password_hash')]);
        Schema::table('users', function (Blueprint $table): void {
            $table->dropColumn([
                'password_hash',
                'status',
                'failed_login_count',
                'locked_until',
                'last_login_at',
                'must_change_password',
                'deleted_at',
            ]);
        });
        Schema::dropIfExists('roles');
    }
};
