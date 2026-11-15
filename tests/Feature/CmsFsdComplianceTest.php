<?php

namespace Tests\Feature;

use App\Models\ActivityLog;
use App\Models\Brand;
use App\Models\ContentBlock;
use App\Models\Documentation;
use App\Models\JobOpening;
use App\Models\Media;
use App\Models\Role;
use App\Models\SocialLink;
use App\Models\User;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CmsFsdComplianceTest extends TestCase
{
    use RefreshDatabase;

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
        Brand::factory()->create(['name' => 'Contoh', 'logo_media_id' => $first->id, 'status' => 'draft']);
        $this->actingAs($superadmin)->get('/admin/media?q=label-first&format=image%2Fpng')->assertOk()->assertSee('first.png')->assertDontSee('second.jpg')->assertSee('Digunakan: Brand');
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
