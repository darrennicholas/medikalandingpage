<?php

namespace Tests\Feature;

use App\Http\Controllers\Api\AdminResourceController;
use App\Models\ContentBlock;
use App\Services\ActivityLogger;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Mockery;
use ReflectionMethod;
use Tests\TestCase;

class ContentBlockRouteTest extends TestCase
{
    public function test_cta_supporting_text_is_optional_for_publication(): void
    {
        $method = new ReflectionMethod(AdminResourceController::class, 'requiredTranslationFields');
        $fields = $method->invoke($this->app->make(AdminResourceController::class), 'content-blocks', new ContentBlock(['block_key' => 'home_cta']));

        $this->assertSame(['title'], $fields);
    }

    public function test_cta_button_urls_accept_internal_and_https_links_and_reject_unsafe_links(): void
    {
        $method = new ReflectionMethod(AdminResourceController::class, 'translationRules');
        $rules = $method->invoke($this->app->make(AdminResourceController::class), 'content-blocks');
        $urlRule = $rules['buttons.*.url'];

        foreach (['/id/kontak', 'https://example.com/contact'] as $url) {
            $this->assertTrue(Validator::make(['buttons' => [['url' => $url]]], ['buttons.*.url' => $urlRule])->passes());
        }
        foreach (['//example.com', 'javascript:alert(1)'] as $url) {
            $this->assertTrue(Validator::make(['buttons' => [['url' => $url]]], ['buttons.*.url' => $urlRule])->fails());
        }
    }

    public function test_content_block_update_passes_resource_and_key_to_controller(): void
    {
        $this->withoutMiddleware();
        $this->partialMock(AdminResourceController::class, function ($controller): void {
            $controller->shouldReceive('update')->once()
                ->with(Mockery::type(Request::class), 'content-blocks', 'home_cta', Mockery::type(ActivityLogger::class))
                ->andReturn(response()->json(['success' => true]));
        });

        $this->putJson('/api/v1/admin/content-blocks/home_cta', [])->assertOk()->assertJson(['success' => true]);
    }

    public function test_content_block_show_passes_resource_and_key_to_controller(): void
    {
        $this->withoutMiddleware();
        $this->partialMock(AdminResourceController::class, function ($controller): void {
            $controller->shouldReceive('show')->once()
                ->with(Mockery::type(Request::class), 'content-blocks', 'home_cta')
                ->andReturn(response()->json(['success' => true]));
        });

        $this->getJson('/api/v1/admin/content-blocks/home_cta')->assertOk()->assertJson(['success' => true]);
    }
}
