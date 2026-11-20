<?php

use App\Http\Controllers\Admin\AuthController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Api\AdminActivityLogController;
use App\Http\Controllers\Api\AdminMediaController;
use App\Http\Controllers\Api\AdminResourceController;
use App\Http\Controllers\Api\AdminUserController;
use App\Http\Controllers\Api\PublicContentApiController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->middleware('cms.public-api-cache')->group(function (): void {
    Route::prefix('public')->name('api.public.')->group(function (): void {
        Route::get('/home', [PublicContentApiController::class, 'home'])->name('home');
        Route::get('/about', [PublicContentApiController::class, 'about'])->name('about');
        Route::get('/product-categories', [PublicContentApiController::class, 'productCategories'])->name('product-categories');
        Route::get('/products/featured', [PublicContentApiController::class, 'featuredProducts'])->name('products.featured');
        Route::get('/products', [PublicContentApiController::class, 'products'])->name('products');
        Route::get('/documentations', [PublicContentApiController::class, 'documentations'])->name('documentations');
        Route::get('/documentations/{slug}', [PublicContentApiController::class, 'documentation'])->name('documentations.show');
        Route::get('/careers', [PublicContentApiController::class, 'careers'])->name('careers');
        Route::get('/careers/{id}', [PublicContentApiController::class, 'career'])->whereNumber('id')->name('careers.show');
        Route::get('/contact', [PublicContentApiController::class, 'contact'])->name('contact');
        Route::get('/footer', [PublicContentApiController::class, 'footer'])->name('footer');
        Route::get('/seo/{pageKey}', [PublicContentApiController::class, 'seo'])->name('seo');
    });

    Route::prefix('admin')->name('api.admin.')->middleware(['web', 'throttle:cms-api'])->group(function (): void {
        Route::post('/auth/login', [AuthController::class, 'apiLogin'])->middleware('throttle:5,1')->name('login');

        Route::middleware(['auth', 'cms.password', 'cms.role:superadmin,admin', 'cms.permission'])->group(function (): void {
            Route::post('/auth/logout', [AuthController::class, 'apiLogout'])->withoutMiddleware('cms.password')->name('logout');
            Route::put('/me/password', [AuthController::class, 'apiUpdatePassword'])->withoutMiddleware('cms.password')->name('password.update');

            Route::get('/dashboard', [DashboardController::class, 'api'])->name('dashboard');
            Route::patch('/{resource}/{id}/status', [AdminResourceController::class, 'updateStatus'])->where('resource', 'hero-slides|brands|mission-items|certificates|product-categories|products|documentations|job-openings|content-blocks|social-links')->whereNumber('id')->name('resources.status');
            Route::put('/{resource}/reorder', [AdminResourceController::class, 'reorder'])->where('resource', 'hero-slides|brands|mission-items|certificates|product-categories|products|documentations|social-links')->name('resources.reorder');
            Route::get('/product-categories/{id}/featured', [AdminResourceController::class, 'featured'])->whereNumber('id')->name('categories.featured.show');
            Route::put('/product-categories/{id}/featured', [AdminResourceController::class, 'featured'])->whereNumber('id')->name('categories.featured.update');
            Route::get('/seo-metas', [AdminResourceController::class, 'index'])->defaults('resource', 'seo-metas')->name('seo.index');
            Route::get('/seo-metas/{id}', [AdminResourceController::class, 'show'])->defaults('resource', 'seo-metas')->name('seo.show');
            Route::put('/seo-metas/{id}', [AdminResourceController::class, 'update'])->defaults('resource', 'seo-metas')->name('seo.update');

            Route::get('/company-summary', [AdminResourceController::class, 'index'])->defaults('resource', 'company-summary')->name('company-summary.show');
            Route::put('/company-summary', [AdminResourceController::class, 'store'])->defaults('resource', 'company-summary')->name('company-summary.update');
            Route::get('/contact-info', [AdminResourceController::class, 'index'])->defaults('resource', 'contact-info')->name('contact-info.show');
            Route::put('/contact-info', [AdminResourceController::class, 'store'])->defaults('resource', 'contact-info')->name('contact-info.update');
            Route::get('/content-blocks/{id}', [AdminResourceController::class, 'showContentBlock'])->name('content-blocks.show');
            Route::put('/content-blocks/{id}', [AdminResourceController::class, 'updateContentBlock'])->name('content-blocks.update');

            Route::get('/media', [AdminMediaController::class, 'index'])->name('media.index');
            Route::post('/media', [AdminMediaController::class, 'store'])->name('media.store');
            Route::put('/media/{media}', [AdminMediaController::class, 'update'])->name('media.update');
            Route::delete('/media/{media}', [AdminMediaController::class, 'destroy'])->name('media.destroy');

            Route::apiResource('users', AdminUserController::class)->except(['store', 'update', 'destroy']);
            Route::post('/users', [AdminUserController::class, 'store'])->name('users.store');
            Route::put('/users/{user}', [AdminUserController::class, 'update'])->name('users.update');
            Route::delete('/users/{user}', [AdminUserController::class, 'destroy'])->name('users.destroy');
            Route::post('/users/{user}/reset-password', [AdminUserController::class, 'resetPassword'])->name('users.reset-password');
            Route::post('/users/{user}/unlock', [AdminUserController::class, 'unlock'])->name('users.unlock');
            Route::get('/activity-logs', [AdminActivityLogController::class, 'index'])->name('activity-logs');

            Route::get('/{resource}', [AdminResourceController::class, 'index'])->where('resource', 'hero-slides|brands|mission-items|certificates|product-categories|products|documentations|job-openings|social-links')->name('resources.index');
            Route::post('/{resource}', [AdminResourceController::class, 'store'])->where('resource', 'hero-slides|brands|mission-items|certificates|product-categories|products|documentations|job-openings|social-links')->name('resources.store');
            Route::get('/{resource}/{id}/preview', [AdminResourceController::class, 'preview'])->where('resource', 'hero-slides|brands|mission-items|certificates|product-categories|products|documentations|job-openings|content-blocks')->name('resources.preview');
            Route::get('/{resource}/{id}', [AdminResourceController::class, 'show'])->where('resource', 'hero-slides|brands|mission-items|certificates|product-categories|products|documentations|job-openings|social-links')->whereNumber('id')->name('resources.show');
            Route::put('/{resource}/{id}', [AdminResourceController::class, 'update'])->where('resource', 'hero-slides|brands|mission-items|certificates|product-categories|products|documentations|job-openings|social-links')->whereNumber('id')->name('resources.update');
            Route::delete('/{resource}/{id}', [AdminResourceController::class, 'destroy'])->where('resource', 'hero-slides|brands|mission-items|certificates|product-categories|products|documentations|job-openings|social-links')->name('resources.destroy');
        });
    });
});
