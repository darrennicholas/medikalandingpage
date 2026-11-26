<?php

namespace Tests\Feature;

use App\Http\Controllers\Admin\PermissionMatrixController;
use App\Models\Brand;
use App\Models\Certificate;
use App\Models\ContentBlock;
use App\Models\HeroSlide;
use App\Models\Media;
use App\Models\MissionItem;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\Role;
use App\Models\SocialLink;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CmsPermissionsTest extends TestCase
{
    use RefreshDatabase;

    public function test_digital_marketing_has_full_default_access_and_can_manage_social_media(): void
    {
        $role = Role::query()->where('name', 'admin')->firstOrFail();
        $this->assertSame(PermissionMatrixController::defaultPermissions(), $role->permissions);
        $admin = User::factory()->create(['role_id' => $role->id]);

        $this->actingAs($admin)->get('/admin/content/social-links')->assertOk();
        $this->get('/admin/brochure-requests')->assertOk()->assertSee('Permintaan Brosur')->assertDontSee('Dashboard Digmar');
        $id = $this->postJson('/api/v1/admin/social-links', ['platform' => 'Instagram', 'url' => 'https://instagram.com/example', 'status' => 'published'])->assertCreated()->json('data.id');
        $this->putJson('/api/v1/admin/social-links/'.$id, ['platform' => 'Instagram', 'url' => 'https://instagram.com/updated', 'status' => 'published'])->assertOk();
        $this->patchJson('/api/v1/admin/social-links/'.$id.'/status', ['status' => 'draft'])->assertOk();
        $this->deleteJson('/api/v1/admin/social-links/'.$id)->assertOk();
        $this->get('/admin/users')->assertOk()->assertSee('Tambah User');
        $this->get('/admin/permissions')->assertOk();
        $this->get('/admin/activity-logs/export')->assertOk();
        $this->get('/admin/users/export')->assertOk();
    }

    public function test_superadmin_can_open_all_cms_menu_pages(): void
    {
        $superadmin = User::factory()->create(['role_id' => Role::query()->where('name', 'superadmin')->value('id')]);
        $paths = [
            '/admin', '/admin/content/hero-slides', '/admin/content/content-blocks?section=home_coverage',
            '/admin/content/content-blocks?section=home_cta', '/admin/content/brands',
            '/admin/content/content-blocks?section=about_company', '/admin/content/company-summary',
            '/admin/content/mission-items', '/admin/content/certificates', '/admin/content/product-categories',
            '/admin/content/products', '/admin/featured-products', '/admin/content/documentations',
            '/admin/content/job-openings', '/admin/content/content-blocks?section=career_intro',
            '/admin/content/contact-info', '/admin/content/social-links', '/admin/media',
            '/admin/content/seo-metas', '/admin/users', '/admin/permissions', '/admin/activity-logs', '/admin/password',
        ];
        foreach ($paths as $path) {
            $bufferLevel = ob_get_level();
            $this->actingAs($superadmin)->get($path)->assertOk();
            $this->assertSame($bufferLevel, ob_get_level(), 'Output buffer tersisa setelah membuka '.$path);
        }
    }

    public function test_changed_admin_brand_permissions_are_enforced_on_subsequent_requests(): void
    {
        $role = Role::query()->where('name', 'admin')->firstOrFail();
        $role->update(['permissions' => ['home_brands' => ['view']]]);
        $admin = User::factory()->create(['role_id' => $role->id]);
        $superadmin = User::factory()->create(['role_id' => Role::query()->where('name', 'superadmin')->value('id')]);
        $brand = Brand::factory()->create(['name' => 'Izin Test', 'status' => 'draft']);

        $this->actingAs($admin)->get('/admin/content/brands')->assertOk();
        $this->postJson('/api/v1/admin/brands', ['name' => 'Baru', 'status' => 'draft'])->assertForbidden();
        $this->putJson('/api/v1/admin/brands/'.$brand->id, ['name' => 'Diubah', 'status' => 'draft'])->assertForbidden();
        $this->deleteJson('/api/v1/admin/brands/'.$brand->id)->assertForbidden();
        $this->patchJson('/api/v1/admin/brands/'.$brand->id.'/status', ['status' => 'published'])->assertForbidden();

        $this->actingAs($superadmin)->put('/admin/permissions', ['permissions' => ['home_brands' => ['view', 'create', 'update', 'delete', 'publish']]])->assertRedirect();
        $this->actingAs($admin->fresh())->putJson('/api/v1/admin/brands/'.$brand->id, ['name' => 'Diubah', 'status' => 'draft'])->assertOk();
        $this->assertSame('Diubah', $brand->fresh()->name);
        $this->actingAs($admin->fresh())->deleteJson('/api/v1/admin/brands/'.$brand->id)->assertOk();
        $this->assertSoftDeleted('brands', ['id' => $brand->id]);
    }

    public function test_each_supported_content_action_checks_its_module_permission(): void
    {
        $role = Role::query()->where('name', 'admin')->firstOrFail();
        $admin = User::factory()->create(['role_id' => $role->id]);
        $resources = [
            'hero-slides' => 'home_hero_banner', 'brands' => 'home_brands', 'mission-items' => 'about_mission',
            'certificates' => 'about_certificates', 'product-categories' => 'products_categories',
            'products' => 'products_catalog', 'documentations' => 'documentation',
            'job-openings' => 'careers', 'social-links' => 'footer',
        ];
        foreach ($resources as $resource => $module) {
            foreach (['view', 'create', 'update', 'delete', 'publish'] as $action) {
                $role->update(['permissions' => []]);
                $request = function () use ($admin, $resource, $action) {
                    $this->actingAs($admin->fresh());
                    $url = '/api/v1/admin/'.$resource;

                    return match ($action) {
                        'view' => $this->getJson($url),
                        'create' => $this->postJson($url, ['status' => 'invalid']),
                        'update' => $this->putJson($url.'/999999', ['status' => 'draft']),
                        'delete' => $this->deleteJson($url.'/999999'),
                        'publish' => $this->patchJson($url.'/999999/status', ['status' => 'invalid']),
                    };
                };
                $request()->assertForbidden();
                $role->update(['permissions' => [$module => [$action]]]);
                $this->assertNotSame(403, $request()->getStatusCode(), $resource.' '.$action.' tetap ditolak meskipun diizinkan');
            }
        }
    }

    public function test_admin_requires_create_and_publish_for_a_published_brand(): void
    {
        $role = Role::query()->where('name', 'admin')->firstOrFail();
        $role->update(['permissions' => ['home_brands' => ['create']]]);
        $admin = User::factory()->create(['role_id' => $role->id]);
        $this->actingAs($admin)->postJson('/api/v1/admin/brands', ['name' => 'Brand', 'status' => 'published'])->assertForbidden();
        $role->update(['permissions' => ['home_brands' => ['create', 'publish']]]);
        $admin->unsetRelation('role');
        $this->actingAs($admin)->postJson('/api/v1/admin/brands', ['name' => 'Brand', 'status' => 'published'])->assertUnprocessable();
    }

    public function test_permission_changes_require_permission_update_access(): void
    {
        $role = Role::query()->where('name', 'admin')->firstOrFail();
        $role->update(['permissions' => []]);
        $admin = User::factory()->create(['role_id' => $role->id]);
        $superadmin = User::factory()->create(['role_id' => Role::query()->where('name', 'superadmin')->value('id')]);
        $this->actingAs($admin)->put('/admin/permissions', ['permissions' => ['media' => ['view']]])->assertForbidden();
        $this->actingAs($superadmin)->put('/admin/permissions', ['permissions' => ['media' => ['view']]])->assertRedirect('/admin/permissions');
        $this->assertSame(['media' => ['view']], $role->fresh()->permissions);
        $this->actingAs($admin->fresh())->get('/admin/media')->assertOk();
        $this->actingAs($admin->fresh())->get('/admin/content/brands')->assertForbidden();
    }

    public function test_matrix_renders_editable_admin_controls_and_fsd_defaults(): void
    {
        $superadmin = User::factory()->create(['role_id' => Role::query()->where('name', 'superadmin')->value('id')]);
        $this->actingAs($superadmin)->get('/admin/permissions')->assertOk()->assertSee('permissions[home_cta][]', false)->assertSee('data-permission-defaults', false)->assertSee('Batalkan Perubahan');
        $defaults = PermissionMatrixController::defaultPermissions();
        $this->assertSame(['view', 'update', 'publish'], $defaults['contacts']);
        $this->assertSame(['view', 'create', 'update', 'delete'], $defaults['users']);
        $this->assertSame(['view', 'update'], $defaults['permissions']);
        $this->assertSame(['view'], $defaults['activity_logs']);
    }

    public function test_non_cms_role_cannot_log_in(): void
    {
        $role = Role::query()->create(['name' => 'user', 'description' => 'Public']);
        $user = User::factory()->create(['role_id' => $role->id, 'password_hash' => 'Password123']);
        $this->post('/admin/login', ['email' => $user->email, 'password' => 'Password123'])->assertSessionHasErrors('email');
        $this->assertGuest();
    }

    public function test_inactive_account_cannot_continue_using_cms(): void
    {
        $admin = User::factory()->inactive()->create(['role_id' => Role::query()->where('name', 'admin')->value('id')]);
        $this->actingAs($admin)->get('/admin')->assertForbidden();
    }

    public function test_all_cms_modules_reject_admin_without_view_permission(): void
    {
        $role = Role::query()->where('name', 'admin')->firstOrFail();
        $role->update(['permissions' => []]);
        $admin = User::factory()->create(['role_id' => $role->id]);
        $paths = ['hero-slides', 'brands', 'company-summary', 'mission-items', 'certificates', 'product-categories', 'products', 'documentations', 'job-openings', 'contact-info', 'social-links', 'seo-metas'];
        foreach ($paths as $resource) {
            $this->actingAs($admin)->get('/admin/content/'.$resource)->assertForbidden();
        }
        foreach (['home_coverage', 'home_cta', 'about_company', 'about_vision', 'career_intro', 'footer_about'] as $block) {
            $this->actingAs($admin)->getJson('/api/v1/admin/content-blocks/'.$block)->assertForbidden();
        }
        $this->actingAs($admin)->get('/admin/media')->assertForbidden();
        $this->actingAs($admin)->get('/admin/featured-products')->assertForbidden();
        $this->actingAs($admin)->get('/admin/users')->assertForbidden();
        $this->actingAs($admin)->get('/admin/activity-logs')->assertForbidden();
    }

    public function test_preview_requires_login_and_permission(): void
    {
        $this->get('/admin/preview/products/1')->assertRedirect('/admin/login');
        $role = Role::query()->where('name', 'admin')->firstOrFail();
        $role->update(['permissions' => []]);
        $admin = User::factory()->create(['role_id' => $role->id]);
        $this->actingAs($admin)->get('/admin/preview/products/1')->assertForbidden();
    }

    public function test_draft_product_preview_is_private_and_not_visible_publicly(): void
    {
        $category = ProductCategory::factory()->create(['status' => 'published']);
        $product = Product::factory()->create(['category_id' => $category->id, 'status' => 'draft']);
        $product->translations()->create(['lang' => 'id', 'name' => 'Produk Rahasia Draft', 'slug' => 'produk-rahasia-draft']);
        $this->get('/id/produk/produk-rahasia-draft')->assertNotFound();
        $superadmin = User::factory()->create(['role_id' => Role::query()->where('name', 'superadmin')->value('id')]);
        $this->actingAs($superadmin)->get('/admin/preview/products/'.$product->id)->assertOk()->assertSee('Produk Rahasia Draft')->assertHeader('X-Robots-Tag', 'noindex, nofollow');
    }

    public function test_catalog_paginates_in_database_and_excludes_drafts(): void
    {
        $category = ProductCategory::factory()->create(['status' => 'published']);
        $category->translations()->create(['lang' => 'id', 'name' => 'Rapid Test', 'slug' => 'rapid-test']);
        for ($i = 1; $i <= 13; $i++) {
            $product = Product::factory()->create(['category_id' => $category->id, 'status' => 'published', 'display_order' => $i]);
            $product->translations()->create(['lang' => 'id', 'name' => 'Produk Uji '.$i, 'slug' => 'produk-uji-'.$i]);
        }
        $draft = Product::factory()->create(['category_id' => $category->id, 'status' => 'draft']);
        $draft->translations()->create(['lang' => 'id', 'name' => 'Produk Tersembunyi', 'slug' => 'produk-tersembunyi']);
        $this->get('/id/produk/semua')->assertOk()->assertSee('Produk Uji 1')->assertDontSee('Produk Uji 13')->assertDontSee('Produk Tersembunyi');
        $this->get('/id/produk/semua?page=2')->assertOk()->assertSee('Produk Uji 13')->assertDontSee('Produk Tersembunyi');
        $otherCategory = ProductCategory::factory()->create(['status' => 'published']);
        $otherCategory->translations()->create(['lang' => 'id', 'name' => 'Kategori Lain', 'slug' => 'kategori-lain']);
        $otherProduct = Product::factory()->create(['category_id' => $otherCategory->id, 'status' => 'published']);
        $otherProduct->translations()->create(['lang' => 'id', 'name' => 'Produk Lain', 'slug' => 'produk-lain']);
        $this->get('/id/produk/semua?category='.$otherCategory->id)->assertOk()->assertSee('Produk Lain')->assertDontSee('Produk Uji 13');
    }

    public function test_content_block_preview_uses_saved_draft_and_preserves_public_version(): void
    {
        $block = ContentBlock::factory()->create(['block_key' => 'home_coverage', 'status' => 'published', 'draft_payload' => ['attributes' => [], 'translations' => ['id' => ['lang' => 'id', 'title' => 'Judul Draft Baru', 'body' => 'Isi Draft']]]]);
        $block->translations()->create(['lang' => 'id', 'title' => 'Judul Tayang Lama', 'body' => 'Isi Tayang']);
        $superadmin = User::factory()->create(['role_id' => Role::query()->where('name', 'superadmin')->value('id')]);
        $this->get('/id')->assertOk()->assertSee('Judul Tayang Lama')->assertDontSee('Judul Draft Baru');
        $this->actingAs($superadmin)->get('/admin/preview/content-blocks/home_coverage')->assertOk()->assertSee('Judul Draft Baru')->assertHeader('X-Robots-Tag', 'noindex, nofollow');
        $this->get('/id')->assertSee('Judul Tayang Lama')->assertDontSee('Judul Draft Baru');
    }

    public function test_hero_preview_displays_pending_draft_without_publishing_it(): void
    {
        $slide = HeroSlide::factory()->create(['status' => 'published', 'draft_payload' => ['attributes' => [], 'translations' => ['id' => ['lang' => 'id', 'title' => 'Banner Draft']]]]);
        $slide->translations()->create(['lang' => 'id', 'title' => 'Banner Published']);
        $superadmin = User::factory()->create(['role_id' => Role::query()->where('name', 'superadmin')->value('id')]);
        $this->get('/id')->assertSee('Banner Published')->assertDontSee('Banner Draft');
        $this->actingAs($superadmin)->get('/admin/preview/hero-slides/'.$slide->id)->assertOk()->assertSee('Banner Draft')->assertHeader('Cache-Control', 'no-store, private');
    }

    public function test_public_product_falls_back_per_field_and_links_to_translated_slug(): void
    {
        $category = ProductCategory::factory()->create(['status' => 'published']);
        $product = Product::factory()->create(['category_id' => $category->id, 'status' => 'published']);
        $product->translations()->create(['lang' => 'id', 'name' => 'Nama Indonesia', 'slug' => 'nama-indonesia', 'description' => 'Deskripsi Indonesia']);
        $product->translations()->create(['lang' => 'en', 'name' => 'English Name', 'slug' => 'english-name', 'description' => '']);
        $this->get('/en/produk/english-name')->assertOk()->assertSee('English Name')->assertSee('Deskripsi Indonesia')->assertSee('/id/produk/nama-indonesia');
        $this->getJson('/api/v1/public/products?lang=en')->assertOk()->assertJsonPath('data.0.translation.description', 'Deskripsi Indonesia');
    }

    public function test_content_block_permission_uses_the_requested_block_not_query_parameter(): void
    {
        $role = Role::query()->where('name', 'admin')->firstOrFail();
        $role->update(['permissions' => ['about_company' => ['view']]]);
        $admin = User::factory()->create(['role_id' => $role->id]);
        $block = ContentBlock::factory()->create(['block_key' => 'home_cta', 'status' => 'draft']);
        $block->translations()->create(['lang' => 'id', 'title' => 'CTA']);
        $this->actingAs($admin)->getJson('/api/v1/admin/content-blocks/home_cta?section=about_company')->assertForbidden();
        $this->actingAs($admin)->getJson('/api/v1/admin/content-blocks/'.$block->id.'/preview?section=about_company')->assertForbidden();
    }

    public function test_navigation_is_present_in_careers_and_product_detail(): void
    {
        $this->get('/id/karir')->assertOk()->assertSee('Dokumentasi')->assertSee('Karir')->assertSee('Kontak');
        $category = ProductCategory::factory()->create(['status' => 'published']);
        $product = Product::factory()->create(['category_id' => $category->id, 'status' => 'published']);
        $product->translations()->create(['lang' => 'id', 'name' => 'Produk Navigasi', 'slug' => 'produk-navigasi']);
        $this->get('/id/produk/produk-navigasi')->assertOk()->assertSee('Dokumentasi')->assertSee('Karir')->assertSee('Kontak');
    }

    public function test_draft_category_and_social_link_preview_use_public_layout(): void
    {
        $category = ProductCategory::factory()->create(['status' => 'draft']);
        $category->translations()->create(['lang' => 'id', 'name' => 'Kategori Draft', 'slug' => 'kategori-draft']);
        $social = SocialLink::query()->create(['platform' => 'Platform Draft', 'url' => 'https://example.com/draft', 'status' => 'draft']);
        $superadmin = User::factory()->create(['role_id' => Role::query()->where('name', 'superadmin')->value('id')]);
        $this->actingAs($superadmin)->get('/admin/preview/product-categories/'.$category->id)->assertOk()->assertSee('Kategori Draft')->assertHeader('X-Robots-Tag', 'noindex, nofollow');
        $this->actingAs($superadmin)->get('/admin/preview/social-links/'.$social->id)->assertOk()->assertSee('Platform Draft')->assertHeader('X-Robots-Tag', 'noindex, nofollow');
        $this->get('/id')->assertDontSee('Platform Draft');
    }

    public function test_draft_hero_brand_mission_and_certificate_are_private_previews(): void
    {
        $media = Media::factory()->create(['file_name' => 'test.png', 'original_name' => 'test.png', 'path' => 'media/test.png', 'mime_type' => 'image/png', 'size_bytes' => 10]);
        $brand = Brand::factory()->create(['name' => 'Brand Draft', 'logo_media_id' => $media->id, 'status' => 'draft']);
        $mission = MissionItem::factory()->create(['status' => 'draft']);
        $mission->translations()->create(['lang' => 'id', 'text' => 'Misi Draft']);
        $certificate = Certificate::factory()->create(['media_id' => $media->id, 'status' => 'draft']);
        $certificate->translations()->create(['lang' => 'id', 'title' => 'Sertifikat Draft']);
        $superadmin = User::factory()->create(['role_id' => Role::query()->where('name', 'superadmin')->value('id')]);
        $this->actingAs($superadmin)->get('/admin/preview/brands/'.$brand->id)->assertOk()->assertSee('Brand Draft');
        $this->actingAs($superadmin)->get('/admin/preview/mission-items/'.$mission->id)->assertOk()->assertSee('Misi Draft');
        $this->actingAs($superadmin)->get('/admin/preview/certificates/'.$certificate->id)->assertOk()->assertSee('Sertifikat Draft');
        $this->get('/id/tentang')->assertDontSee('Misi Draft')->assertDontSee('Sertifikat Draft');
    }
}
