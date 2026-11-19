<?php

namespace Tests\Feature;

use App\Models\ActivityLog;
use App\Models\Brand;
use App\Models\Certificate;
use App\Models\ContactInfo;
use App\Models\ContentBlock;
use App\Models\Documentation;
use App\Models\HeroSlide;
use App\Models\JobOpening;
use App\Models\Media;
use App\Models\MissionItem;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\Role;
use App\Models\SeoMeta;
use App\Models\SocialLink;
use App\Models\User;
use App\Services\ContentVersion;
use App\Services\PublicRichText;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Routing\Middleware\ThrottleRequests;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class CmsFsdComplianceTest extends TestCase
{
    use RefreshDatabase;

    public function test_same_second_concurrent_edits_are_rejected_without_overwriting_first_save(): void
    {
        $admin = $this->cmsUser('superadmin');
        $brand = Brand::factory()->create(['name' => 'Nama awal', 'status' => 'draft']);
        $version = app(ContentVersion::class)->token($brand);
        $url = '/api/v1/admin/brands/'.$brand->id;
        $this->actingAs($admin)->putJson($url, ['status' => 'draft', 'name' => 'Perubahan pertama', 'expected_version' => $version])->assertOk();
        $this->putJson($url, ['status' => 'draft', 'name' => 'Perubahan kedua', 'expected_version' => $version])->assertStatus(409)->assertJsonPath('message', 'Data telah diubah pengguna lain. Muat ulang halaman lalu terapkan perubahan kembali.');
        $this->assertSame('Perubahan pertama', $brand->fresh()->name);
    }

    public function test_concurrent_translation_edits_are_rejected_even_with_same_timestamp(): void
    {
        $admin = $this->cmsUser('superadmin');
        $block = ContentBlock::factory()->create(['block_key' => 'about_company', 'status' => 'draft']);
        $block->translations()->create(['lang' => 'id', 'title' => 'Judul awal', 'body' => 'Isi awal']);
        $version = app(ContentVersion::class)->token($block);
        $url = '/api/v1/admin/content-blocks/about_company';
        $this->actingAs($admin)->putJson($url, ['block_key' => 'about_company', 'status' => 'draft', 'translations' => ['id' => ['title' => 'Judul pertama']], 'expected_version' => $version])->assertOk();
        $this->putJson($url, ['block_key' => 'about_company', 'status' => 'draft', 'translations' => ['id' => ['title' => 'Judul kedua']], 'expected_version' => $version])->assertStatus(409);
        $this->assertSame('Judul pertama', $block->translations()->where('lang', 'id')->value('title'));
    }

    public function test_cms_exports_are_limited_to_superadmin_and_honor_filters(): void
    {
        $superadmin = $this->cmsUser('superadmin');
        $admin = $this->cmsUser('admin', ['users' => ['view'], 'activity_logs' => ['view']]);
        $admin->update(['name' => 'Ekspor Terpilih']);
        $response = $this->actingAs($superadmin)->get('/admin/users/export?q=Ekspor%20Terpilih')->assertOk()->assertDownload();
        $this->assertStringContainsString('Ekspor Terpilih', $response->streamedContent());
        $this->assertStringNotContainsString($superadmin->email, $response->streamedContent());
        $this->get('/admin/activity-logs/export')->assertOk()->assertDownload();
        $this->actingAs($admin)->get('/admin/users/export')->assertForbidden();
        $this->get('/admin/activity-logs/export')->assertForbidden();
    }

    public function test_hero_and_content_block_forms_expose_unpublished(): void
    {
        $admin = $this->cmsUser('superadmin');
        $this->actingAs($admin)->get('/admin/content/hero-slides/create')->assertOk()->assertSee('data-set-status="unpublished"', false);
        foreach (['home_coverage', 'home_cta', 'about_company', 'career_intro'] as $section) {
            $this->get('/admin/content/content-blocks?section='.$section)->assertOk()->assertSee('data-set-status="unpublished"', false);
        }
        $this->get('/admin/content/mission-items')->assertOk()->assertSee('data-set-status="unpublished"', false);
        $this->get('/admin/content/social-links')->assertOk()->assertSee('value="unpublished"', false);
    }

    public function test_unpublishing_hero_and_coverage_removes_them_from_public_home(): void
    {
        $admin = $this->cmsUser('superadmin');
        $image = $this->media('publish-status.png');
        $hero = HeroSlide::factory()->create(['status' => 'published', 'media_id' => $image->id]);
        $hero->translations()->create(['lang' => 'id', 'title' => 'Banner status unik']);
        $coverage = ContentBlock::factory()->create(['block_key' => 'home_coverage', 'status' => 'published', 'media_id' => $image->id]);
        $coverage->translations()->create(['lang' => 'id', 'title' => 'Jangkauan status unik']);
        $this->get('/id')->assertOk()->assertSee('Banner status unik')->assertSee('Jangkauan status unik');
        $this->actingAs($admin)->putJson('/api/v1/admin/hero-slides/'.$hero->id, ['status' => 'unpublished', 'expected_version' => app(ContentVersion::class)->token($hero)])->assertOk();
        $this->putJson('/api/v1/admin/content-blocks/home_coverage', ['block_key' => 'home_coverage', 'status' => 'unpublished', 'expected_version' => app(ContentVersion::class)->token($coverage)])->assertOk();
        $this->get('/id')->assertOk()->assertDontSee('Banner status unik')->assertDontSee('Jangkauan status unik');
    }

    public function test_admin_api_has_a_separate_general_rate_limit(): void
    {
        $admin = $this->cmsUser('superadmin');
        $this->actingAs($admin);
        for ($request = 0; $request < 120; $request++) {
            $this->putJson('/api/v1/admin/me/password', [])->assertUnprocessable();
        }
        $this->putJson('/api/v1/admin/me/password', [])->assertTooManyRequests()->assertJsonPath('message', 'Terlalu banyak permintaan. Silakan coba lagi nanti.');
    }

    public function test_published_certificate_rejects_clearing_required_values_but_draft_allows_them(): void
    {
        $admin = $this->cmsUser('superadmin');
        $image = $this->media('certificate-required.png');
        $certificate = Certificate::factory()->create(['status' => 'published', 'media_id' => $image->id]);
        $certificate->translations()->create(['lang' => 'id', 'title' => 'Judul sertifikat']);
        $url = '/api/v1/admin/certificates/'.$certificate->id;

        $this->actingAs($admin)->putJson($url, ['status' => 'published', 'translations' => ['id' => ['title' => null]]])->assertUnprocessable()->assertJsonValidationErrors('translations.id.title');
        $this->putJson($url, ['status' => 'published', 'media_id' => null])->assertUnprocessable()->assertJsonValidationErrors('media_id');
        $this->assertSame('Judul sertifikat', $certificate->fresh()->translations->firstWhere('lang', 'id')->title);
        $this->assertSame($image->id, $certificate->fresh()->media_id);
        $this->putJson($url, ['status' => 'published', 'issued_year' => 2024])->assertOk();
        $this->putJson($url, ['status' => 'draft', 'media_id' => null, 'translations' => ['id' => ['title' => null]]])->assertOk();
    }

    public function test_inactive_accounts_remain_inactive_and_login_failures_are_generic(): void
    {
        $this->withoutMiddleware(ThrottleRequests::class);
        $inactive = $this->cmsUser('admin');
        $inactive->update(['status' => 'inactive']);
        $url = '/api/v1/admin/auth/login';
        foreach (range(1, 6) as $attempt) {
            $this->postJson($url, ['email' => $inactive->email, 'password' => 'wrong'])->assertUnprocessable()->assertJsonPath('message', 'Email atau password salah.');
        }
        $this->travel(16)->minutes();
        $this->postJson($url, ['email' => $inactive->email, 'password' => 'password'])->assertUnprocessable()->assertJsonPath('message', 'Email atau password salah.');
        $this->assertSame('inactive', $inactive->fresh()->status);
        $this->postJson('/admin/login', ['email' => $inactive->email, 'password' => 'password'])->assertUnprocessable()->assertJsonPath('message', 'Email atau password salah.');
        $this->postJson($url, ['email' => 'unknown@example.test', 'password' => 'wrong'])->assertUnprocessable()->assertJsonPath('message', 'Email atau password salah.');

        $active = $this->cmsUser('admin');
        foreach (range(1, 5) as $attempt) {
            $this->postJson($url, ['email' => $active->email, 'password' => 'wrong'])->assertUnprocessable()->assertJsonPath('message', 'Email atau password salah.');
        }
        $this->assertSame('locked', $active->fresh()->status);
        $this->postJson($url, ['email' => $active->email, 'password' => 'password'])->assertUnprocessable()->assertJsonPath('message', 'Email atau password salah.');
        $this->travel(16)->minutes();
        $this->postJson($url, ['email' => $active->email, 'password' => 'password'])->assertOk();
        $this->assertSame('active', $active->fresh()->status);
    }

    public function test_language_links_use_localized_pages_instead_of_custom_canonical_urls(): void
    {
        SeoMeta::query()->create(['page_key' => 'about', 'lang' => 'id', 'canonical_url' => 'https://example.com/canonical-id', 'robots_index' => true]);
        SeoMeta::query()->create(['page_key' => 'about', 'lang' => 'en', 'canonical_url' => 'https://example.com/canonical-en', 'robots_index' => true]);
        $this->get('/id/tentang')->assertOk()->assertSee('<link rel="canonical" href="https://example.com/canonical-id">', false)->assertSee('hreflang="en" href="'.url('/en/tentang').'"', false);
        $this->get('/en/tentang')->assertOk()->assertSee('hreflang="id" href="'.url('/id/tentang').'"', false);

        $category = ProductCategory::factory()->create(['status' => 'published']);
        $product = Product::factory()->create(['category_id' => $category->id, 'status' => 'published']);
        $product->translations()->create(['lang' => 'id', 'name' => 'Produk', 'slug' => 'produk-id']);
        $product->translations()->create(['lang' => 'en', 'name' => 'Product', 'slug' => 'product-en']);
        $this->get('/id/produk/produk-id')->assertOk()->assertSee('hreflang="en" href="'.url('/en/produk/product-en').'"', false);
        $this->get('/en/produk/product-en')->assertOk()->assertSee('hreflang="id" href="'.url('/id/produk/produk-id').'"', false);
    }

    public function test_public_categories_hide_empty_and_sitemap_uses_fallback_slugs(): void
    {
        $empty = ProductCategory::factory()->create(['status' => 'published']);
        $empty->translations()->create(['lang' => 'id', 'name' => 'Kategori Kosong Unik', 'slug' => 'kategori-kosong-unik']);
        $category = ProductCategory::factory()->create(['status' => 'published']);
        $category->translations()->create(['lang' => 'id', 'name' => 'Kategori Terisi Unik', 'slug' => 'kategori-terisi-unik']);
        $product = Product::factory()->create(['category_id' => $category->id, 'status' => 'published']);
        $product->translations()->create(['lang' => 'id', 'name' => 'Produk Unik', 'slug' => 'produk-unik']);
        $article = Documentation::factory()->create(['type' => 'article', 'status' => 'published']);
        $article->translations()->create(['lang' => 'id', 'title' => 'Artikel Unik', 'slug' => 'artikel-unik']);
        $this->get('/id/produk')->assertOk()->assertSee('Kategori Terisi Unik')->assertDontSee('Kategori Kosong Unik')->assertSee('data-catalog-link', false);
        $this->get('/sitemap.xml')->assertOk()->assertSee('/id/produk/produk-unik')->assertSee('/en/produk/produk-unik')->assertSee('/id/dokumentasi/artikel-unik')->assertSee('/en/dokumentasi/artikel-unik');
        $this->get('/en/produk/produk-unik')->assertOk();
        $this->get('/en/dokumentasi/artikel-unik')->assertOk();
    }

    public function test_public_rich_text_preserves_format_and_removes_unsafe_content(): void
    {
        $result = PublicRichText::render('<p onclick="evil()"><strong>Tebal</strong><a href="javascript:evil()">Link</a><script>evil()</script></p>');
        $this->assertStringContainsString('<strong>Tebal</strong>', $result);
        $this->assertStringNotContainsString('onclick', $result);
        $this->assertStringNotContainsString('javascript', $result);
        $this->assertStringNotContainsString('evil()', $result);
    }

    public function test_public_pages_render_english_interface(): void
    {
        $this->get('/en')->assertOk()->assertSee('Home')->assertSee('Products');
        $this->get('/en/tentang')->assertOk()->assertSee('MEDICAL DEVICE MANUFACTURER');
        $this->get('/en/produk')->assertOk()->assertSee('Discover Our Products')->assertSee('All Products');
        $this->get('/en/produk/semua')->assertOk()->assertSee('Search products...')->assertSee('Back to Featured Products');
        $this->get('/en/dokumentasi')->assertOk()->assertSee('DOCUMENTATION')->assertSee('Read more');
        $this->get('/en/karir')->assertOk()->assertSee('Explore opportunities');
        $this->get('/en/kontak')->assertOk()->assertSee('Contact Us');
        $this->get('/id/produk')->assertOk()->assertSee('Temukan Produk Terbaik Kami');
    }

    public function test_english_home_falls_back_to_indonesian_metrics_and_buttons(): void
    {
        $coverage = ContentBlock::factory()->create(['block_key' => 'home_coverage', 'status' => 'published']);
        $coverage->translations()->create(['lang' => 'id', 'title' => 'Jangkauan', 'metrics' => [['value' => '38', 'label' => 'Provinsi']]]);
        $coverage->translations()->create(['lang' => 'en', 'title' => 'Coverage']);
        $cta = ContentBlock::factory()->create(['block_key' => 'home_cta', 'status' => 'published']);
        $cta->translations()->create(['lang' => 'id', 'title' => 'Kontak', 'buttons' => [['label' => 'Hubungi Kami', 'url' => '/kontak']]]);
        $cta->translations()->create(['lang' => 'en', 'title' => 'Contact']);

        $this->get('/en')->assertOk()->assertSee('Provinsi')->assertSee('Hubungi Kami');
    }

    public function test_failed_login_locks_a_cms_account_after_five_attempts(): void
    {
        $user = $this->cmsUser('admin');
        for ($attempt = 1; $attempt <= 5; $attempt++) {
            $response = $this->postJson('/admin/login', ['email' => $user->email, 'password' => 'incorrect-password']);
            $response->assertUnprocessable();
            if ($attempt < 5) {
                $response->assertJsonPath('message', 'Email atau password salah.');
            }
        }
        $this->assertSame('locked', $user->fresh()->status);
        $this->assertNotNull($user->fresh()->locked_until);
    }

    public function test_featured_products_reject_more_than_six(): void
    {
        $admin = $this->cmsUser('superadmin');
        $category = ProductCategory::factory()->create(['status' => 'published']);
        $products = Product::factory()->count(7)->create(['category_id' => $category->id, 'status' => 'published']);
        $url = '/api/v1/admin/product-categories/'.$category->id.'/featured';
        $this->actingAs($admin)->putJson($url, ['product_ids' => $products->take(6)->modelKeys()])->assertOk();
        $this->putJson($url, ['product_ids' => $products->modelKeys()])->assertUnprocessable();
        $this->assertCount(6, $category->fresh()->featuredProducts);
    }

    public function test_featured_product_is_removed_when_saved_as_draft_or_moved_to_another_category(): void
    {
        $admin = $this->cmsUser('superadmin');
        $image = $this->media('featured-sync.png');
        $oldCategory = ProductCategory::factory()->create(['status' => 'published']);
        $oldCategory->translations()->create(['lang' => 'id', 'name' => 'Kategori awal', 'slug' => 'kategori-awal']);
        $newCategory = ProductCategory::factory()->create(['status' => 'published']);
        $newCategory->translations()->create(['lang' => 'id', 'name' => 'Kategori baru', 'slug' => 'kategori-baru']);
        $product = Product::factory()->create(['status' => 'published', 'category_id' => $oldCategory->id, 'media_id' => $image->id]);
        $product->translations()->create(['lang' => 'id', 'name' => 'Produk pilihan', 'slug' => 'produk-pilihan', 'description' => 'Deskripsi']);
        $oldCategory->featuredProducts()->attach($product->id, ['display_order' => 1]);
        $url = '/api/v1/admin/products/'.$product->id;

        $this->actingAs($admin)->putJson($url, ['status' => 'draft'])->assertOk();
        $this->assertDatabaseMissing('category_featured_products', ['category_id' => $oldCategory->id, 'product_id' => $product->id]);
        $this->patchJson($url.'/status', ['status' => 'published'])->assertOk();
        $this->assertDatabaseMissing('category_featured_products', ['category_id' => $oldCategory->id, 'product_id' => $product->id]);
        $oldCategory->featuredProducts()->attach($product->id, ['display_order' => 1]);
        $this->putJson($url, ['status' => 'published', 'category_id' => $newCategory->id])->assertOk();
        $this->assertDatabaseMissing('category_featured_products', ['category_id' => $oldCategory->id, 'product_id' => $product->id]);
        $this->assertSame($newCategory->id, $product->fresh()->category_id);
        $this->get('/id/produk')->assertOk()->assertSee('Produk pilihan')->assertSee('Kategori baru');
        $other = Product::factory()->create(['status' => 'published', 'category_id' => $oldCategory->id, 'media_id' => $image->id]);
        $other->translations()->create(['lang' => 'id', 'name' => 'Produk kategori awal', 'slug' => 'produk-kategori-awal']);
        $oldCategory->featuredProducts()->attach($product->id, ['display_order' => 1]);
        $this->get('/id/produk')->assertOk()->assertViewHas('categories', function (array $categories) use ($oldCategory): bool {
            $old = collect($categories)->firstWhere('id', (string) $oldCategory->id);

            return collect($old['products'])->pluck('name')->all() === ['Produk kategori awal'];
        });
    }

    public function test_media_usage_lists_hero_seo_and_draft_content(): void
    {
        $admin = $this->cmsUser('superadmin');
        $image = $this->media('usage-check.png');
        HeroSlide::factory()->create(['media_id' => $image->id, 'status' => 'draft']);
        SeoMeta::query()->create(['page_key' => 'about', 'lang' => 'id', 'og_media_id' => $image->id, 'robots_index' => true]);
        $this->actingAs($admin)->get('/admin/media?q=usage-check')->assertOk()->assertSee('Hero / Banner (Draft)')->assertSee('SEO Open Graph: Tentang (ID)');
        $this->get('/admin/media?section=home')->assertOk()->assertSee('usage-check.png');
        $this->get('/admin/media?section=about')->assertOk()->assertSee('usage-check.png');
        $this->deleteJson('/api/v1/admin/media/'.$image->id)->assertUnprocessable();

        $draftImage = $this->media('pending-draft.png');
        HeroSlide::factory()->create(['status' => 'published', 'draft_payload' => ['attributes' => ['media_id' => $draftImage->id]]]);
        $this->get('/admin/media?q=pending-draft')->assertOk()->assertSee('Hero / Banner (Draft perubahan)');
        $this->deleteJson('/api/v1/admin/media/'.$draftImage->id)->assertUnprocessable()->assertJsonPath('data.usages.0', 'Draft perubahan Hero');
    }

    public function test_reorder_logs_include_before_and_after_for_content_and_featured_products(): void
    {
        $admin = $this->cmsUser('superadmin');
        $first = Brand::factory()->create(['display_order' => 1]);
        $second = Brand::factory()->create(['display_order' => 2]);
        $this->actingAs($admin)->putJson('/api/v1/admin/brands/reorder', ['ids' => [$second->id, $first->id]])->assertOk();
        $log = ActivityLog::query()->where('action', 'reorder')->where('entity_type', Brand::class)->latest('id')->firstOrFail();
        $this->assertSame([$first->id, $second->id], $log->old_values['ids']);
        $this->assertSame([$second->id, $first->id], $log->new_values['ids']);

        $category = ProductCategory::factory()->create(['status' => 'published']);
        $products = Product::factory()->count(2)->create(['category_id' => $category->id, 'status' => 'published']);
        $category->featuredProducts()->attach($products[0]->id, ['display_order' => 1]);
        $this->putJson('/api/v1/admin/product-categories/'.$category->id.'/featured', ['product_ids' => [$products[1]->id]])->assertOk();
        $log = ActivityLog::query()->where('action', 'reorder')->where('entity_type', ProductCategory::class)->latest('id')->firstOrFail();
        $this->assertSame([$products[0]->id], $log->old_values['featured_product_ids']);
        $this->assertSame([$products[1]->id], $log->new_values['featured_product_ids']);
        $this->putJson('/api/v1/admin/product-categories/'.$category->id.'/featured', ['product_ids' => [$products[0]->id, $products[1]->id]])->assertOk();
        $this->putJson('/api/v1/admin/product-categories/'.$category->id.'/featured', ['product_ids' => [$products[1]->id, $products[0]->id]])->assertOk();
        $log = ActivityLog::query()->where('action', 'reorder')->where('entity_type', ProductCategory::class)->latest('id')->firstOrFail();
        $this->assertSame([$products[0]->id, $products[1]->id], $log->old_values['featured_product_ids']);
        $this->assertSame([$products[1]->id, $products[0]->id], $log->new_values['featured_product_ids']);
    }

    public function test_media_rejects_invalid_or_oversized_upload_and_prevents_deleting_used_image(): void
    {
        Storage::fake(config('cms.media.disk'));
        $admin = $this->cmsUser('superadmin');
        $this->actingAs($admin)->postJson('/api/v1/admin/media', ['files' => [UploadedFile::fake()->create('invalid.pdf', 10, 'application/pdf')]])->assertUnprocessable();
        $this->actingAs($admin)->postJson('/api/v1/admin/media', ['files' => [UploadedFile::fake()->image('large.jpg')->size(2049)]])->assertUnprocessable();
        $image = $this->media('used.png');
        Brand::factory()->create(['logo_media_id' => $image->id, 'status' => 'published']);
        $this->actingAs($admin)->deleteJson('/api/v1/admin/media/'.$image->id)->assertUnprocessable()->assertJsonPath('data.usages.0', 'Brand');
        $this->assertDatabaseHas('media', ['id' => $image->id, 'deleted_at' => null]);
    }

    public function test_category_uniqueness_and_inactive_product_category_validation(): void
    {
        $admin = $this->cmsUser('superadmin');
        $category = ProductCategory::factory()->create(['status' => 'published']);
        $category->translations()->create(['lang' => 'id', 'name' => 'Nama Unik', 'slug' => 'nama-unik']);
        $this->actingAs($admin)->postJson('/api/v1/admin/product-categories', ['status' => 'published', 'translations' => ['id' => ['name' => 'Nama Unik', 'slug' => 'nama-lain']]])->assertUnprocessable()->assertJsonValidationErrors('translations.id.name');
        $inactive = ProductCategory::factory()->create(['status' => 'draft']);
        $this->postJson('/api/v1/admin/products', ['category_id' => $inactive->id, 'status' => 'draft', 'translations' => ['id' => ['name' => 'Produk Uji']]])->assertUnprocessable()->assertJsonValidationErrors('category_id');
    }

    public function test_last_mission_cannot_be_deleted(): void
    {
        $admin = $this->cmsUser('superadmin');
        $mission = MissionItem::factory()->create(['status' => 'published']);
        $this->actingAs($admin)->deleteJson('/api/v1/admin/mission-items/'.$mission->id)->assertUnprocessable();
        $this->assertDatabaseHas('mission_items', ['id' => $mission->id, 'deleted_at' => null]);
    }

    public function test_media_alt_text_starts_empty_and_can_be_saved_in_both_languages(): void
    {
        Storage::fake(config('cms.media.disk'));
        $superadmin = $this->cmsUser('superadmin');
        $response = $this->actingAs($superadmin)->postJson('/api/v1/admin/media', [
            'files' => [UploadedFile::fake()->image('gambar.png', 30, 30)],
        ])->assertCreated();
        $id = $response->json('data.0.id');
        $this->assertDatabaseHas('media', ['id' => $id, 'alt_text_id' => null, 'alt_text_en' => null]);
        $this->actingAs($superadmin)->get('/admin/media')->assertOk()->assertSee('Alt Indonesia')->assertSee('Alt English')->assertSee('Simpan Alt Text');
        $this->actingAs($superadmin)->putJson('/api/v1/admin/media/'.$id, [
            'alt_text_id' => 'Alat pemeriksaan darah', 'alt_text_en' => 'Blood testing equipment',
        ])->assertOk();
        $this->assertDatabaseHas('media', ['id' => $id, 'alt_text_id' => 'Alat pemeriksaan darah', 'alt_text_en' => 'Blood testing equipment']);
        $this->actingAs($superadmin)->putJson('/api/v1/admin/media/'.$id, ['alt_text_id' => '', 'alt_text_en' => ''])->assertOk();
        $this->assertDatabaseHas('media', ['id' => $id, 'alt_text_id' => null, 'alt_text_en' => null]);
    }

    public function test_product_partial_drafts_are_saved_without_category_but_cannot_be_published(): void
    {
        $admin = $this->cmsUser('superadmin');
        $payload = ['status' => 'draft', 'category_id' => null, 'translations' => ['id' => ['name' => null, 'slug' => null, 'description' => null], 'en' => ['name' => null, 'slug' => null, 'description' => null]]];
        $first = $this->actingAs($admin)->postJson('/api/v1/admin/products', $payload)->assertCreated()->json('data.id');
        $second = $this->postJson('/api/v1/admin/products', $payload)->assertCreated()->json('data.id');
        $this->assertDatabaseHas('products', ['id' => $first, 'category_id' => null, 'status' => 'draft']);
        $this->assertDatabaseHas('product_translations', ['product_id' => $second, 'lang' => 'id', 'slug' => null]);
        $this->patchJson('/api/v1/admin/products/'.$first.'/status', ['status' => 'published'])->assertUnprocessable();
        $payload['buy_url'] = 'not-a-valid-url';
        $this->postJson('/api/v1/admin/products', $payload)->assertUnprocessable()->assertJsonValidationErrors('buy_url');
        $this->get('/admin/content/products/create')->assertOk()->assertSee('data-save-status="draft" formnovalidate', false);
    }

    public function test_new_product_defaults_to_existing_logo_pair_and_can_be_changed(): void
    {
        $superadmin = $this->cmsUser('superadmin');
        $category = ProductCategory::factory()->create(['status' => 'published']);
        $top = $this->media('top-logo.png');
        $bottom = $this->media('watermark.png');
        $alternative = $this->media('different-logo.png');
        Product::factory()->count(2)->create(['category_id' => $category->id, 'logo_top_media_id' => $top->id, 'logo_bottom_media_id' => $bottom->id]);
        $existing = Product::factory()->create(['category_id' => $category->id, 'logo_top_media_id' => $alternative->id, 'logo_bottom_media_id' => null]);

        $this->actingAs($superadmin)->get('/admin/content/products/create')->assertOk()->assertViewHas('record', fn ($record) => $record->logo_top_media_id === $top->id && $record->logo_bottom_media_id === $bottom->id);
        $this->actingAs($superadmin)->get('/admin/content/products/'.$existing->id.'/edit')->assertOk()->assertViewHas('record', fn ($record) => $record->logo_top_media_id === $alternative->id && $record->logo_bottom_media_id === null);
        $this->actingAs($superadmin)->postJson('/api/v1/admin/products', [
            'category_id' => $category->id, 'status' => 'draft',
            'logo_top_media_id' => $alternative->id, 'logo_bottom_media_id' => $bottom->id,
            'translations' => ['id' => ['name' => 'Produk Uji']],
        ])->assertCreated()->assertJsonPath('data.logo_top_media_id', $alternative->id)->assertJsonPath('data.logo_bottom_media_id', $bottom->id);
    }

    public function test_jobs_created_and_updated_in_cms_are_reflected_on_careers_page(): void
    {
        $superadmin = $this->cmsUser('superadmin');
        $payload = [
            'status' => 'open', 'employment_type' => 'Full Time', 'location' => 'Jakarta',
            'posted_at' => today()->toDateString(), 'closing_date' => today()->addDays(30)->toDateString(),
            'apply_url' => 'https://example.com/apply',
            'translations' => ['id' => ['title' => 'Analis Laboratorium', 'description' => 'Bergabung dengan tim laboratorium.', 'requirements' => 'Pengalaman laboratorium.']],
        ];
        $response = $this->actingAs($superadmin)->postJson('/api/v1/admin/job-openings', $payload)->assertCreated();
        $id = $response->json('data.id');
        $this->get('/id/karir')->assertOk()->assertSee('Analis Laboratorium')->assertSee('Jakarta')->assertSee('Full Time');
        $payload['translations']['id']['title'] = 'Analis Medis Senior';
        $this->actingAs($superadmin)->putJson('/api/v1/admin/job-openings/'.$id, $payload)->assertOk();
        $this->get('/id/karir')->assertSee('Analis Medis Senior')->assertDontSee('Analis Laboratorium');
        $this->actingAs($superadmin)->patchJson('/api/v1/admin/job-openings/'.$id.'/status', ['status' => 'closed'])->assertOk();
        $this->get('/id/karir')->assertDontSee('Analis Medis Senior')->assertSee('Belum ada lowongan yang dibuka');
    }

    private function cmsUser(string $roleName, array $permissions = []): User
    {
        $role = Role::query()->where('name', $roleName)->firstOrFail();
        if ($roleName === 'admin') {
            $role->update(['permissions' => $permissions]);
        }

        return User::factory()->create(['role_id' => $role->id]);
    }

    private function media(string $name, string $mime = 'image/png'): Media
    {
        return Media::factory()->create(['file_name' => $name, 'original_name' => $name, 'path' => 'media/'.$name, 'mime_type' => $mime, 'size_bytes' => 20, 'alt_text_id' => 'label-'.$name]);
    }

    public function test_admin_can_edit_published_social_link_but_cannot_publish_a_draft(): void
    {
        $admin = $this->cmsUser('admin', ['footer' => ['view', 'update']]);
        $active = SocialLink::query()->create(['platform' => 'Instagram', 'url' => 'https://example.com/old', 'status' => 'published']);
        $draft = SocialLink::query()->create(['platform' => 'LinkedIn', 'url' => 'https://example.com/draft', 'status' => 'draft']);
        $this->actingAs($admin)->putJson('/api/v1/admin/social-links/'.$active->id, ['platform' => 'Instagram', 'url' => 'https://example.com/new', 'status' => 'published'])->assertOk();
        $this->assertSame('https://example.com/new', $active->fresh()->url);
        $this->actingAs($admin)->putJson('/api/v1/admin/social-links/'.$draft->id, ['platform' => 'LinkedIn', 'url' => 'https://example.com/draft', 'status' => 'published'])->assertForbidden();
        $this->actingAs($admin)->patchJson('/api/v1/admin/social-links/'.$active->id.'/status', ['status' => 'unpublished'])->assertForbidden();
    }

    public function test_superadmin_cannot_assign_a_public_role_to_cms_user(): void
    {
        $superadmin = $this->cmsUser('superadmin');
        $publicRole = Role::query()->create(['name' => 'user', 'description' => 'Public']);
        $this->actingAs($superadmin)->postJson('/api/v1/admin/users', ['name' => 'Example', 'email' => 'new@example.com', 'role_id' => $publicRole->id, 'status' => 'active', 'password' => 'Password123', 'password_confirmation' => 'Password123'])->assertUnprocessable()->assertJsonValidationErrors('role_id');
        $this->actingAs($superadmin)->get('/admin/users/create')->assertOk()->assertDontSee('User (akses publik saja)');
    }

    public function test_media_can_be_filtered_by_format_and_alt_text_and_shows_usage(): void
    {
        $superadmin = $this->cmsUser('superadmin');
        $first = $this->media('first.png');
        $this->media('second.jpg', 'image/jpeg');
        Brand::factory()->create(['name' => 'Contoh', 'logo_media_id' => $first->id, 'status' => 'published']);
        $this->actingAs($superadmin)->get('/admin/media?q=label-first&format=image%2Fpng')->assertOk()->assertSee('first.png')->assertDontSee('second.jpg')->assertSee('Digunakan di: Beranda');
    }

    public function test_media_alt_text_is_used_in_public_sections_and_usage_shows_location(): void
    {
        $superadmin = $this->cmsUser('superadmin');
        $media = Media::factory()->create([
            'file_name' => 'gambartentangmedika.jpeg', 'original_name' => 'gambartentangmedika.jpeg',
            'path' => 'images/gambartentangmedika.jpeg', 'mime_type' => 'image/jpeg', 'size_bytes' => 10,
        ]);
        $block = ContentBlock::factory()->create(['block_key' => 'about_company', 'media_id' => $media->id, 'status' => 'published']);
        $block->translations()->create(['lang' => 'id', 'title' => 'Tentang Kami']);
        $hero = HeroSlide::factory()->create(['media_id' => $media->id, 'status' => 'published']);
        $hero->translations()->create(['lang' => 'id', 'title' => 'Beranda']);
        $this->actingAs($superadmin)->putJson('/api/v1/admin/media/'.$media->id, [
            'alt_text_id' => 'Foto tim perusahaan', 'alt_text_en' => 'Company team photo',
        ])->assertOk();
        $this->get('/id')->assertOk()->assertSee('class="hero__image"', false)->assertSee('alt="Foto tim perusahaan"', false);
        $this->get('/id/tentang')->assertOk()->assertSee('alt="Foto tim perusahaan"', false);
        $this->get('/id/karir')->assertOk()->assertSee('alt="Foto tim perusahaan"', false);
        $this->get('/en/karir')->assertOk()->assertSee('alt="Company team photo"', false);
        $this->actingAs($superadmin)->get('/admin/media')->assertOk()->assertSee('Digunakan di: Tentang, Karir');
        $this->get('/admin/media?section=about')->assertOk()->assertSee('gambartentangmedika.jpeg');
        $this->get('/admin/media?section=home')->assertOk()->assertDontSee('gambartentangmedika.jpeg');
    }

    public function test_reordering_brands_is_saved_and_logged(): void
    {
        $admin = $this->cmsUser('admin', ['home_brands' => ['view', 'update']]);
        $first = Brand::factory()->create(['name' => 'Pertama', 'display_order' => 1, 'status' => 'draft']);
        $second = Brand::factory()->create(['name' => 'Kedua', 'display_order' => 2, 'status' => 'draft']);
        $this->actingAs($admin)->putJson('/api/v1/admin/brands/reorder', ['ids' => [$second->id, $first->id]])->assertOk();
        $this->assertSame(1, $second->fresh()->display_order);
        $this->assertSame(2, $first->fresh()->display_order);
        $this->assertDatabaseHas('activity_logs', ['user_id' => $admin->id, 'action' => 'reorder']);
    }

    public function test_documentation_gallery_order_is_persisted(): void
    {
        $superadmin = $this->cmsUser('superadmin');
        $first = $this->media('first.png');
        $second = $this->media('second.png');
        $document = Documentation::factory()->create(['type' => 'article', 'status' => 'draft']);
        $this->actingAs($superadmin)->putJson('/api/v1/admin/documentations/'.$document->id, ['status' => 'draft', 'type' => 'article', 'gallery_present' => true, 'gallery' => [['media_id' => $second->id], ['media_id' => $first->id]]])->assertOk();
        $this->assertSame([$second->id, $first->id], $document->fresh()->gallery->modelKeys());
    }

    public function test_dashboard_displays_job_counts(): void
    {
        $superadmin = $this->cmsUser('superadmin');
        JobOpening::factory()->create(['status' => 'open', 'closing_date' => today()->addDays(2)]);
        JobOpening::factory()->create(['status' => 'closed']);
        $this->actingAs($superadmin)->get('/admin')->assertOk()->assertViewHas('jobStats', ['open' => 1, 'closed' => 1, 'closingSoon' => 1]);
    }

    public function test_cms_rejects_unsafe_cta_links_and_overlong_mission_or_vision(): void
    {
        $superadmin = $this->cmsUser('superadmin');
        $this->actingAs($superadmin)->postJson('/api/v1/admin/mission-items', ['status' => 'draft', 'translations' => ['id' => ['text' => str_repeat('a', 301)]]])->assertUnprocessable()->assertJsonValidationErrors('translations.id.text');
        $this->actingAs($superadmin)->postJson('/api/v1/admin/hero-slides', ['status' => 'draft', 'cta_url' => 'http://example.com'])->assertUnprocessable()->assertJsonValidationErrors('cta_url');
        $vision = ContentBlock::query()->create(['block_key' => 'about_vision', 'status' => 'draft']);
        $this->actingAs($superadmin)->putJson('/api/v1/admin/content-blocks/about_vision', ['block_key' => 'about_vision', 'status' => 'draft', 'translations' => ['id' => ['title' => 'Visi', 'body' => str_repeat('a', 501)]]])->assertUnprocessable()->assertJsonValidationErrors('translations.id.body');
        $this->assertSame('draft', $vision->fresh()->status);
    }

    public function test_activity_log_records_translations_before_and_after_update(): void
    {
        $superadmin = $this->cmsUser('superadmin');
        $document = Documentation::factory()->create(['type' => 'article', 'status' => 'draft']);
        $document->translations()->create(['lang' => 'id', 'title' => 'Judul Lama', 'slug' => 'judul-lama']);
        $this->actingAs($superadmin)->putJson('/api/v1/admin/documentations/'.$document->id, ['status' => 'draft', 'type' => 'article', 'translations' => ['id' => ['title' => 'Judul Baru', 'slug' => 'judul-baru']]])->assertOk();
        $log = ActivityLog::query()->where('entity_type', Documentation::class)->latest('id')->firstOrFail();
        $this->assertSame('Judul Lama', $log->old_values['translations'][0]['title']);
        $this->assertSame('Judul Baru', $log->new_values['translations'][0]['title']);
    }

    public function test_rich_text_removes_unsafe_attributes_and_schemes(): void
    {
        $admin = $this->cmsUser('superadmin');
        $document = Documentation::factory()->create(['type' => 'article', 'status' => 'draft']);
        $this->actingAs($admin)->putJson('/api/v1/admin/documentations/'.$document->id, [
            'status' => 'draft', 'type' => 'article',
            'translations' => ['id' => ['title' => 'Artikel', 'body' => '<p onclick="alert(1)">Aman <a href="java&#x73;cript:alert(1)">bahaya</a><a href="https://example.com">tautan</a><svg><script>alert(1)</script></svg></p>']],
        ])->assertOk();
        $body = $document->fresh()->translations()->where('lang', 'id')->value('body');
        $this->assertStringNotContainsString('onclick', $body);
        $this->assertStringNotContainsString('javascript', $body);
        $this->assertStringNotContainsString('alert', $body);
        $this->assertStringContainsString('href="https://example.com"', $body);
    }

    public function test_scheduled_closure_records_each_expired_job_once(): void
    {
        $expired = JobOpening::factory()->create(['status' => 'open', 'closing_date' => today()->subDay()]);
        $current = JobOpening::factory()->create(['status' => 'open', 'closing_date' => today()]);
        $event = collect(app(Schedule::class)->events())->last();
        $event->run(app());
        $event->run(app());
        $this->assertSame('closed', $expired->fresh()->status);
        $this->assertSame('open', $current->fresh()->status);
        $logs = ActivityLog::query()->where('entity_type', JobOpening::class)->where('entity_id', $expired->id)->get();
        $this->assertCount(1, $logs);
        $this->assertSame(['status' => 'open'], $logs->first()->old_values);
        $this->assertSame(['status' => 'closed'], $logs->first()->new_values);
    }

    public function test_publication_lists_filter_by_year_and_sort(): void
    {
        foreach (['article', 'activity'] as $type) {
            foreach ([2024, 2025] as $year) {
                $item = Documentation::factory()->create(['type' => $type, 'status' => 'published', 'event_date' => $year.'-05-10']);
                $item->translations()->create(['lang' => 'id', 'title' => ucfirst($type).' '.$year, 'slug' => $type.'-'.$year]);
            }
            $url = $type === 'article' ? '/id/artikel' : '/id/berita';
            $this->get($url.'?year=2024')->assertOk()->assertSee(ucfirst($type).' 2024')->assertDontSee(ucfirst($type).' 2025');
            $this->get($url.'?sort=oldest')->assertOk()->assertSeeInOrder([ucfirst($type).' 2024', ucfirst($type).' 2025']);
        }
    }

    public function test_published_cms_article_appears_in_article_listing_with_shared_card(): void
    {
        $admin = $this->cmsUser('superadmin');
        $cover = $this->media('article-cover.png');

        $this->actingAs($admin)->postJson('/api/v1/admin/documentations', [
            'type' => 'article', 'status' => 'published', 'cover_media_id' => $cover->id,
            'event_date' => '2026-11-16',
            'translations' => ['id' => ['title' => 'Artikel dari CMS', 'slug' => 'artikel-dari-cms', 'excerpt' => 'Ringkasan artikel', 'body' => '<p>Isi artikel</p>']],
        ])->assertSuccessful();

        $this->get('/id/artikel')->assertOk()->assertSeeInOrder(['Artikel dari CMS', 'Ringkasan artikel', '16 Nov 2026', 'Baca Selengkapnya'])->assertSee('/id/dokumentasi/artikel-dari-cms')->assertDontSee($admin->name);
        $this->get('/id/berita')->assertDontSee('Artikel dari CMS');
        $this->get('/id/dokumentasi/artikel-dari-cms')->assertOk()->assertSee('Isi artikel');
    }

    public function test_documentation_has_separate_article_and_news_creation_forms(): void
    {
        $admin = $this->cmsUser('superadmin');
        $cover = $this->media('news-cover.png');
        $this->actingAs($admin)->get('/admin/content/documentations')->assertOk()->assertSee('Tambah Artikel')->assertSee('Tambah Berita / Foto Kegiatan');
        $this->get('/admin/content/documentations/create?type=article')->assertOk()->assertSee('Tambah Artikel')->assertSee('name="type" value="article"', false)->assertSee('Isi Artikel');
        $this->get('/admin/content/documentations/create?type=activity')->assertOk()->assertSee('Tambah Berita / Foto Kegiatan')->assertSee('name="type" value="activity"', false)->assertSee('Isi Berita');
        $this->postJson('/api/v1/admin/documentations', [
            'type' => 'activity', 'status' => 'published', 'cover_media_id' => $cover->id,
            'event_date' => '2026-11-16', 'gallery_present' => true, 'gallery' => [['media_id' => $cover->id]],
            'translations' => ['id' => ['title' => 'Berita dari CMS', 'slug' => 'berita-dari-cms']],
        ])->assertSuccessful();
        $this->get('/id/berita')->assertOk()->assertSee('Berita dari CMS');
        $this->get('/id/artikel')->assertDontSee('Berita dari CMS');
    }

    public function test_footer_settings_preserve_status_and_require_publish_permission_for_changes(): void
    {
        $admin = $this->cmsUser('admin', ['footer' => ['view', 'update']]);
        $footer = ContentBlock::factory()->create(['block_key' => 'footer_about', 'status' => 'published']);

        $this->actingAs($admin)->get('/admin/content/social-links')->assertOk()->assertSee('value="unpublished"', false)->assertSee('© '.now()->year.' PT Interskala Medika Indonesia | Member of Interskala Group');
        $this->putJson('/api/v1/admin/content-blocks/footer_about', ['block_key' => 'footer_about', 'status' => 'published', 'translations' => ['id' => ['body' => 'Ringkasan baru', 'copyright_text' => 'Hak cipta baru']]])->assertOk();
        $this->assertSame('published', $footer->fresh()->status);
        $this->get('/admin/content/social-links')->assertSee('value="Hak cipta baru"', false);
        $this->get('/id')->assertSee('Hak cipta baru');
        $this->putJson('/api/v1/admin/content-blocks/footer_about', ['block_key' => 'footer_about', 'status' => 'unpublished'])->assertForbidden();
    }

    public function test_hero_footer_and_log_show_cms_data(): void
    {
        $admin = $this->cmsUser('superadmin');
        foreach (['one', 'two'] as $index => $name) {
            $image = $this->media($name.'.png');
            $slide = HeroSlide::factory()->create(['media_id' => $image->id, 'status' => 'published', 'display_order' => $index + 1]);
            $slide->translations()->create(['lang' => 'id', 'title' => 'Slide '.$name]);
        }
        ContactInfo::factory()->create(['phone' => '+62 123456', 'email' => 'office@example.com']);
        SocialLink::query()->create(['platform' => 'Instagram', 'url' => 'https://example.com/instagram', 'status' => 'published']);
        ActivityLog::query()->create(['user_id' => $admin->id, 'action' => 'update', 'entity_type' => 'Product', 'entity_id' => 1, 'old_values' => ['status' => 'draft'], 'new_values' => ['status' => 'published'], 'ip_address' => '192.0.2.12', 'created_at' => now()]);
        $this->get('/id')->assertOk()->assertSee('one.png')->assertSee('two.png')->assertSee('data-hero-next', false)->assertSee('office@example.com')->assertSee('Instagram');
        $this->actingAs($admin)->get('/admin/activity-logs')->assertOk()->assertSeeInOrder(['Product #1', 'status', '192.0.2.12']);
    }

    public function test_footer_contact_visibility_is_configurable_without_changing_contact_page(): void
    {
        $admin = $this->cmsUser('admin', ['footer' => ['view', 'update']]);
        $footer = ContentBlock::factory()->create(['block_key' => 'footer_about', 'status' => 'published']);
        ContactInfo::factory()->create(['phone' => '+62 123456', 'email' => 'office@example.com']);
        $this->actingAs($admin)->get('/admin/content/social-links')->assertOk()->assertSee('name="footer_show_phone"', false)->assertSee('name="footer_show_email"', false);
        $this->putJson('/api/v1/admin/content-blocks/footer_about', ['block_key' => 'footer_about', 'status' => 'published', 'footer_show_phone' => false, 'footer_show_email' => true])->assertOk();
        $this->assertFalse($footer->fresh()->footer_show_phone);
        foreach (['/id', '/en', '/id/produk', '/id/kontak'] as $url) {
            $html = $this->get($url)->assertOk()->getContent();
            $footerHtml = explode('<footer', $html, 2)[1];
            $this->assertStringNotContainsString('+62 123456', $footerHtml);
            $this->assertStringContainsString('office@example.com', $footerHtml);
        }
        $this->get('/id/kontak')->assertSee('+62 123456');
        $this->putJson('/api/v1/admin/content-blocks/footer_about', ['block_key' => 'footer_about', 'status' => 'published', 'footer_show_phone' => true, 'footer_show_email' => false])->assertOk();
        $html = $this->get('/id')->assertOk()->getContent();
        $footerHtml = explode('<footer', $html, 2)[1];
        $this->assertStringContainsString('+62 123456', $footerHtml);
        $this->assertStringNotContainsString('office@example.com', $footerHtml);
    }

    public function test_contact_map_uses_coordinates_and_falls_back_to_existing_location_url(): void
    {
        $contact = ContactInfo::factory()->create(['phone' => '+62 123456', 'email' => 'map@example.com', 'maps_url' => 'https://www.google.com/maps?q=company', 'latitude' => null, 'longitude' => null]);
        $this->get('/id/kontak')->assertOk()->assertSee('<iframe', false)->assertDontSee('data-contact-map', false);
        $contact->update(['latitude' => -6.15, 'longitude' => 106.72]);
        $this->get('/id/kontak')->assertOk()->assertSee('data-contact-map', false)->assertSee('data-latitude="-6.15', false)->assertSee('data-longitude="106.72', false)->assertSee('Buka petunjuk arah');
        $this->get('/en/kontak')->assertOk()->assertSee('Open directions')->assertSee('data-contact-map', false);
    }

    public function test_hero_status_publication_requires_cta_url(): void
    {
        $admin = $this->cmsUser('superadmin');
        $slide = HeroSlide::factory()->create(['status' => 'draft', 'media_id' => $this->media('hero-cta.png')->id]);
        $slide->translations()->create(['lang' => 'id', 'title' => 'Banner', 'cta_label' => 'Selengkapnya']);
        $this->actingAs($admin)->patchJson('/api/v1/admin/hero-slides/'.$slide->id.'/status', ['status' => 'published'])->assertUnprocessable();
        $this->assertSame('draft', $slide->fresh()->status);
    }

    public function test_published_activity_can_remove_all_gallery_images(): void
    {
        $admin = $this->cmsUser('superadmin');
        $cover = $this->media('activity.png');
        $item = Documentation::factory()->create(['type' => 'activity', 'status' => 'published', 'cover_media_id' => $cover->id, 'event_date' => today()]);
        $item->translations()->create(['lang' => 'id', 'title' => 'Berita', 'slug' => 'berita']);
        $item->gallery()->attach($cover->id, ['display_order' => 1]);
        $this->actingAs($admin)->putJson('/api/v1/admin/documentations/'.$item->id, ['type' => 'activity', 'status' => 'published', 'event_date' => today()->toDateString(), 'gallery_present' => true, 'gallery' => []])->assertOk();
        $this->assertCount(0, $item->fresh()->gallery);
        $this->actingAs($admin)->get('/admin/content/documentations/'.$item->id.'/edit')->assertSee('value="'.today()->toDateString().'"', false);
        $this->patchJson('/api/v1/admin/documentations/'.$item->id.'/status', ['status' => 'unpublished'])->assertOk();
        $this->patchJson('/api/v1/admin/documentations/'.$item->id.'/status', ['status' => 'published'])->assertOk();
        $this->get('/id/berita')->assertSee('Berita');
    }

    public function test_unsaved_content_preview_does_not_create_records(): void
    {
        $admin = $this->cmsUser('superadmin');
        $this->actingAs($admin)->get('/admin/content/products/create')->assertDontSee('data-preview-unsaved', false);
        $this->post('/admin/preview/hero-slides', ['translations' => ['id' => ['title' => 'Banner preview']]])->assertOk()->assertSee('Banner preview')->assertHeader('X-Robots-Tag', 'noindex, nofollow');
        $this->post('/admin/preview/products', ['translations' => ['id' => ['name' => 'Produk preview']]])->assertOk()->assertSee('Produk preview')->assertHeader('X-Robots-Tag', 'noindex, nofollow');
        $this->post('/admin/preview/documentations', ['type' => 'article', 'translations' => ['id' => ['title' => 'Artikel preview', 'body' => '<script>alert(1)</script><p>Aman</p>']]])->assertOk()->assertSee('Artikel preview')->assertDontSee('<script>alert(1)</script>', false);
        $this->post('/admin/preview/job-openings', ['translations' => ['id' => ['title' => 'Karir preview']]])->assertOk()->assertSee('Karir preview');
        $this->assertSame(0, Product::query()->count());
        $this->assertSame(0, Documentation::query()->count());
        $this->assertSame(0, JobOpening::query()->count());
        auth()->logout();
        $this->post('/admin/preview/products', [])->assertRedirect('/admin/login');
    }

    public function test_unsaved_document_preview_uses_english_and_gallery_captions(): void
    {
        $admin = $this->cmsUser('superadmin');
        $photo = $this->media('preview-gallery.png');
        $this->actingAs($admin)->post('/admin/preview/documentations', [
            'type' => 'article', 'preview_lang' => 'en', 'gallery_media_ids' => [$photo->id],
            'gallery_captions' => [$photo->id => ['id' => 'Keterangan Indonesia', 'en' => 'English caption']],
            'translations' => ['id' => ['title' => 'Judul Indonesia', 'body' => 'Isi Indonesia'], 'en' => ['title' => 'English title']],
        ])->assertOk()->assertSee('English title')->assertSee('Isi Indonesia')->assertSee('English caption')->assertHeader('X-Robots-Tag', 'noindex, nofollow');
        $this->assertSame(0, Documentation::query()->count());
    }

    public function test_unsaved_category_brand_and_mission_previews_do_not_save_content(): void
    {
        $admin = $this->cmsUser('superadmin');
        $logo = $this->media('brand-preview.png');
        $this->actingAs($admin)->post('/admin/preview/product-categories', ['translations' => ['id' => ['name' => 'Kategori preview']]])->assertOk()->assertSee('Kategori preview');
        $this->post('/admin/preview/brands', ['name' => 'Brand preview', 'logo_media_id' => $logo->id])->assertOk()->assertSee('brand-preview.png');
        $this->post('/admin/preview/mission-items', ['translations' => ['id' => ['text' => 'Misi preview']]])->assertOk()->assertSee('Misi preview');
        $this->assertSame(0, ProductCategory::query()->count());
        $this->assertSame(0, Brand::query()->count());
        $this->assertSame(0, MissionItem::query()->count());
    }

    public function test_public_product_and_news_links_follow_updated_slugs(): void
    {
        $category = ProductCategory::factory()->create(['status' => 'published']);
        $category->translations()->create(['lang' => 'id', 'name' => 'Kategori', 'slug' => 'kategori']);
        $product = Product::factory()->create(['category_id' => $category->id, 'status' => 'published']);
        $product->translations()->create(['lang' => 'id', 'name' => 'Produk baru', 'slug' => 'produk-baru']);
        $news = Documentation::factory()->create(['type' => 'activity', 'status' => 'published', 'event_date' => today()]);
        $news->translations()->create(['lang' => 'id', 'title' => 'Berita baru', 'slug' => 'berita-baru']);
        $this->get('/id/produk')->assertOk()->assertSee('/id/produk/produk-baru');
        $this->get('/id/berita')->assertOk()->assertSee('/id/berita/berita-baru');
        $this->get('/id/berita/berita-baru')->assertOk()->assertSee('Berita baru');
        $this->get('/id/dokumentasi/kegiatan/'.$news->id)->assertOk();
    }

    public function test_hero_english_cta_requires_link_when_saving_or_publishing(): void
    {
        $admin = $this->cmsUser('superadmin');
        $image = $this->media('english-hero.png');
        $draft = HeroSlide::factory()->create(['status' => 'draft', 'media_id' => $image->id]);
        $draft->translations()->create(['lang' => 'id', 'title' => 'Judul']);
        $draft->translations()->create(['lang' => 'en', 'title' => 'Title', 'cta_label' => 'Read more']);
        $this->actingAs($admin)->patchJson('/api/v1/admin/hero-slides/'.$draft->id.'/status', ['status' => 'published'])->assertUnprocessable();
        $this->putJson('/api/v1/admin/hero-slides/'.$draft->id, ['status' => 'published', 'translations' => ['id' => ['title' => 'Judul']]])->assertUnprocessable()->assertJsonValidationErrors('cta_url');
        $this->putJson('/api/v1/admin/hero-slides/'.$draft->id, ['status' => 'published', 'cta_url' => '/id/kontak', 'translations' => ['id' => ['title' => 'Judul']]])->assertOk();
        $this->putJson('/api/v1/admin/hero-slides/'.$draft->id, ['status' => 'published', 'cta_url' => null, 'translations' => ['id' => ['title' => 'Judul']]])->assertUnprocessable()->assertJsonValidationErrors('cta_url');
    }

    public function test_certificate_can_be_previewed_in_both_languages_without_saving(): void
    {
        $admin = $this->cmsUser('superadmin');
        $image = $this->media('certificate-preview.png');
        $payload = ['media_id' => $image->id, 'issued_year' => '2025', 'translations' => ['id' => ['title' => 'Sertifikat contoh', 'description' => 'Keterangan'], 'en' => ['title' => 'Example certificate', 'description' => 'Description']]];
        $this->actingAs($admin)->get('/admin/content/certificates/create')->assertOk()->assertDontSee('Pratinjau ID')->assertDontSee('Pratinjau EN');
        $this->post('/admin/preview/certificates', $payload + ['preview_lang' => 'id'])->assertOk()->assertSee('Sertifikat contoh')->assertSee('certificate-preview.png')->assertHeader('X-Robots-Tag', 'noindex, nofollow')->assertHeader('Cache-Control', 'no-store, private');
        $this->post('/admin/preview/certificates', $payload + ['preview_lang' => 'en'])->assertOk()->assertSee('Example certificate')->assertSee('Description');
        $this->assertDatabaseCount('certificates', 0);
        auth()->logout();
        $this->post('/admin/preview/certificates', $payload)->assertRedirect('/admin/login');
    }
}
