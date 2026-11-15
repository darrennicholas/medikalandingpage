<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $addAuditColumns = static function (Blueprint $table): void {
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();
        };

        Schema::create('content_blocks', function (Blueprint $table) use ($addAuditColumns): void {
            $table->id();
            $table->string('block_key', 50)->unique();
            $table->foreignId('media_id')->nullable()->constrained('media')->restrictOnDelete();
            $table->string('cta_url', 500)->nullable();
            $table->string('status', 20)->default('draft');
            $addAuditColumns($table);
        });
        Schema::create('content_block_translations', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('content_block_id')->constrained()->cascadeOnDelete();
            $table->char('lang', 2);
            $table->string('title', 200)->nullable();
            $table->string('subtitle', 300)->nullable();
            $table->text('body')->nullable();
            $table->string('cta_label', 60)->nullable();
            $table->unique(['content_block_id', 'lang']);
        });

        Schema::create('hero_slides', function (Blueprint $table) use ($addAuditColumns): void {
            $table->id();
            $table->foreignId('media_id')->nullable()->constrained('media')->restrictOnDelete();
            $table->string('cta_url', 500)->nullable();
            $table->unsignedInteger('display_order')->default(1);
            $table->string('status', 20)->default('draft');
            $addAuditColumns($table);
        });
        Schema::create('hero_slide_translations', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('hero_slide_id')->constrained()->cascadeOnDelete();
            $table->char('lang', 2);
            $table->string('title', 120)->nullable();
            $table->string('subtitle', 250)->nullable();
            $table->string('cta_label', 40)->nullable();
            $table->unique(['hero_slide_id', 'lang']);
        });

        Schema::create('brands', function (Blueprint $table) use ($addAuditColumns): void {
            $table->id();
            $table->string('name', 100)->nullable();
            $table->foreignId('logo_media_id')->nullable()->constrained('media')->restrictOnDelete();
            $table->string('website_url', 500)->nullable();
            $table->unsignedInteger('display_order')->default(1);
            $table->string('status', 20)->default('draft');
            $addAuditColumns($table);
        });

        Schema::create('mission_items', function (Blueprint $table) use ($addAuditColumns): void {
            $table->id();
            $table->unsignedInteger('display_order')->default(1);
            $table->string('status', 20)->default('draft');
            $addAuditColumns($table);
        });
        Schema::create('mission_item_translations', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('mission_item_id')->constrained()->cascadeOnDelete();
            $table->char('lang', 2);
            $table->string('text', 500)->nullable();
            $table->unique(['mission_item_id', 'lang']);
        });

        Schema::create('company_summary', function (Blueprint $table): void {
            $table->id();
            $table->unsignedSmallInteger('established_year');
            $table->unsignedInteger('partner_count');
            $table->unsignedTinyInteger('province_count');
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        Schema::create('certificates', function (Blueprint $table) use ($addAuditColumns): void {
            $table->id();
            $table->foreignId('media_id')->nullable()->constrained('media')->restrictOnDelete();
            $table->unsignedSmallInteger('issued_year')->nullable();
            $table->unsignedInteger('display_order')->default(1);
            $table->string('status', 20)->default('draft');
            $addAuditColumns($table);
        });
        Schema::create('certificate_translations', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('certificate_id')->constrained()->cascadeOnDelete();
            $table->char('lang', 2);
            $table->string('title', 150)->nullable();
            $table->text('description')->nullable();
            $table->unique(['certificate_id', 'lang']);
        });

        Schema::create('product_categories', function (Blueprint $table) use ($addAuditColumns): void {
            $table->id();
            $table->unsignedInteger('display_order')->default(1);
            $table->string('status', 20)->default('draft');
            $addAuditColumns($table);
        });
        Schema::create('product_category_translations', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('product_category_id')->constrained()->cascadeOnDelete();
            $table->char('lang', 2);
            $table->string('name', 80)->nullable();
            $table->string('slug', 100)->nullable();
            $table->unique(['product_category_id', 'lang']);
            $table->unique(['lang', 'slug']);
        });

        Schema::create('products', function (Blueprint $table) use ($addAuditColumns): void {
            $table->id();
            $table->foreignId('category_id')->nullable()->constrained('product_categories')->restrictOnDelete();
            $table->foreignId('media_id')->nullable()->constrained('media')->restrictOnDelete();
            $table->string('brochure_path', 500)->nullable();
            $table->string('buy_url', 500)->nullable();
            $table->json('parameters')->nullable();
            $table->string('subcategory', 100)->nullable();
            $table->foreignId('logo_top_media_id')->nullable()->constrained('media')->restrictOnDelete();
            $table->foreignId('logo_bottom_media_id')->nullable()->constrained('media')->restrictOnDelete();
            $table->unsignedInteger('display_order')->default(1);
            $table->string('status', 20)->default('draft');
            $addAuditColumns($table);
        });
        Schema::create('product_translations', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->char('lang', 2);
            $table->string('name', 150)->nullable();
            $table->string('slug', 150)->nullable();
            $table->text('description')->nullable();
            $table->unique(['product_id', 'lang']);
            $table->unique(['lang', 'slug']);
        });
        Schema::create('category_featured_products', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('category_id')->constrained('product_categories')->cascadeOnDelete();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->unsignedTinyInteger('display_order');
            $table->unique(['category_id', 'product_id']);
            $table->unique(['category_id', 'display_order']);
        });

        Schema::create('documentations', function (Blueprint $table) use ($addAuditColumns): void {
            $table->id();
            $table->string('type', 20)->nullable();
            $table->foreignId('cover_media_id')->nullable()->constrained('media')->restrictOnDelete();
            $table->date('event_date')->nullable();
            $table->string('status', 20)->default('draft');
            $addAuditColumns($table);
        });
        Schema::create('documentation_translations', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('documentation_id')->constrained()->cascadeOnDelete();
            $table->char('lang', 2);
            $table->string('title', 150)->nullable();
            $table->string('slug', 150)->nullable();
            $table->string('excerpt', 250)->nullable();
            $table->longText('body')->nullable();
            $table->unique(['documentation_id', 'lang']);
            $table->unique(['lang', 'slug']);
        });
        Schema::create('documentation_media', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('documentation_id')->constrained()->cascadeOnDelete();
            $table->foreignId('media_id')->constrained('media')->restrictOnDelete();
            $table->string('caption_id', 200)->nullable();
            $table->string('caption_en', 200)->nullable();
            $table->unsignedInteger('display_order')->default(1);
            $table->unique(['documentation_id', 'media_id']);
        });

        Schema::create('job_openings', function (Blueprint $table) use ($addAuditColumns): void {
            $table->id();
            $table->string('employment_type', 50)->nullable();
            $table->string('location', 150)->nullable();
            $table->date('posted_at')->nullable();
            $table->date('closing_date')->nullable();
            $table->string('apply_url', 500)->nullable();
            $table->string('status', 20)->default('draft');
            $table->string('source', 20)->default('manual');
            $table->string('external_ref', 100)->nullable()->unique();
            $addAuditColumns($table);
        });
        Schema::create('job_opening_translations', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('job_opening_id')->constrained()->cascadeOnDelete();
            $table->char('lang', 2);
            $table->string('title', 150)->nullable();
            $table->text('description')->nullable();
            $table->text('requirements')->nullable();
            $table->unique(['job_opening_id', 'lang']);
        });

        Schema::create('contact_info', function (Blueprint $table): void {
            $table->id();
            $table->string('phone', 30);
            $table->string('email', 150);
            $table->string('maps_url', 500)->nullable();
            $table->decimal('latitude', 10, 7)->nullable();
            $table->decimal('longitude', 10, 7)->nullable();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
        Schema::create('contact_info_translations', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('contact_info_id')->constrained('contact_info')->cascadeOnDelete();
            $table->char('lang', 2);
            $table->text('address');
            $table->string('office_hours', 150)->nullable();
            $table->unique(['contact_info_id', 'lang']);
        });
        Schema::create('social_links', function (Blueprint $table) use ($addAuditColumns): void {
            $table->id();
            $table->string('platform', 30);
            $table->string('url', 500);
            $table->unsignedInteger('display_order')->default(1);
            $table->string('status', 20)->default('draft');
            $addAuditColumns($table);
        });

        Schema::create('coverage_regions', function (Blueprint $table): void {
            $table->id();
            $table->string('province_code', 10)->unique();
            $table->string('province_name', 100);
            $table->boolean('is_covered')->default(false);
            $table->decimal('latitude', 10, 7);
            $table->decimal('longitude', 10, 7);
            $table->string('note', 300)->nullable();
        });
        Schema::create('seo_metas', function (Blueprint $table): void {
            $table->id();
            $table->string('page_key', 50);
            $table->char('lang', 2);
            $table->string('meta_title', 70)->nullable();
            $table->string('meta_description', 160)->nullable();
            $table->string('canonical_url', 500)->nullable();
            $table->foreignId('og_media_id')->nullable()->constrained('media')->restrictOnDelete();
            $table->boolean('robots_index')->default(true);
            $table->unique(['page_key', 'lang']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('seo_metas');
        Schema::dropIfExists('coverage_regions');
        Schema::dropIfExists('social_links');
        Schema::dropIfExists('contact_info_translations');
        Schema::dropIfExists('contact_info');
        Schema::dropIfExists('job_opening_translations');
        Schema::dropIfExists('job_openings');
        Schema::dropIfExists('documentation_media');
        Schema::dropIfExists('documentation_translations');
        Schema::dropIfExists('documentations');
        Schema::dropIfExists('category_featured_products');
        Schema::dropIfExists('product_translations');
        Schema::dropIfExists('products');
        Schema::dropIfExists('product_category_translations');
        Schema::dropIfExists('product_categories');
        Schema::dropIfExists('certificate_translations');
        Schema::dropIfExists('certificates');
        Schema::dropIfExists('company_summary');
        Schema::dropIfExists('mission_item_translations');
        Schema::dropIfExists('mission_items');
        Schema::dropIfExists('brands');
        Schema::dropIfExists('hero_slide_translations');
        Schema::dropIfExists('hero_slides');
        Schema::dropIfExists('content_block_translations');
        Schema::dropIfExists('content_blocks');
    }
};
