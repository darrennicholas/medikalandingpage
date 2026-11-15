<?php

namespace Tests\Feature;

use App\Models\ActivityLog;
use App\Models\Brand;
use App\Models\ContentBlock;
use App\Models\Documentation;
use App\Models\HeroSlide;
use App\Models\JobOpening;
use App\Models\Media;
use App\Models\MissionItem;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\Role;
use App\Models\SocialLink;
use App\Models\User;
use App\Services\PublicRichText;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class CmsFsdComplianceTest extends TestCase
{
    use RefreshDatabase;

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
        $this->get('/id')->assertOk()->assertDontSee('class="hero__image"', false);
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
}
