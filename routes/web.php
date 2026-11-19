<?php

use App\Http\Controllers\Admin\ActivityLogController;
use App\Http\Controllers\Admin\AuthController;
use App\Http\Controllers\Admin\ContentManagementController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\FeaturedProductsController;
use App\Http\Controllers\Admin\MediaLibraryController;
use App\Http\Controllers\Admin\PermissionMatrixController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\MediaController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\PublicPageController;
use App\Http\Controllers\SitemapController;
use App\Http\Middleware\LocalizeChinesePublicPage;
use App\Http\Middleware\SetPublicLanguage;
use Illuminate\Support\Facades\Route;

Route::prefix('admin')->name('admin.')->group(function (): void {
    Route::get('/login', [AuthController::class, 'create'])->name('login');
    Route::post('/login', [AuthController::class, 'store'])->middleware('throttle:5,1')->name('login.store');

    Route::middleware(['auth', 'cms.password', 'cms.role:superadmin,admin', 'cms.permission'])->group(function (): void {
        Route::post('/logout', [AuthController::class, 'destroy'])->withoutMiddleware('cms.password')->name('logout');
        Route::get('/password', [AuthController::class, 'editPassword'])->name('password.edit');
        Route::put('/password', [AuthController::class, 'updatePassword'])->name('password.update');
        Route::get('/', [DashboardController::class, 'index'])->name('dashboard');
        Route::get('/featured-products', [FeaturedProductsController::class, 'index'])->name('featured.index');
        Route::post('/preview/{resource}', [PublicPageController::class, 'previewUnsaved'])->where('resource', 'products|documentations|job-openings|hero-slides|brands|mission-items|product-categories|certificates')->name('content.preview-unsaved');
        Route::get('/preview/{resource}/{id}', [PublicPageController::class, 'preview'])->where('resource', 'products|product-categories|documentations|job-openings|hero-slides|content-blocks|brands|mission-items|certificates|social-links')->name('content.preview');
        Route::get('/content/{resource}', [ContentManagementController::class, 'index'])->where('resource', 'hero-slides|content-blocks|brands|mission-items|company-summary|certificates|product-categories|products|documentations|job-openings|contact-info|social-links|seo-metas')->name('content.index');
        Route::get('/content/{resource}/create', [ContentManagementController::class, 'create'])->where('resource', 'hero-slides|content-blocks|brands|mission-items|company-summary|certificates|product-categories|products|documentations|job-openings|contact-info|social-links|seo-metas')->name('content.create');
        Route::get('/content/{resource}/{id}', [ContentManagementController::class, 'show'])->where('resource', 'hero-slides')->name('content.show');
        Route::get('/content/{resource}/{id}/edit', [ContentManagementController::class, 'edit'])->where('resource', 'hero-slides|content-blocks|brands|mission-items|company-summary|certificates|product-categories|products|documentations|job-openings|contact-info|social-links|seo-metas')->name('content.edit');
        Route::get('/media', [MediaLibraryController::class, 'index'])->name('media.index');
        Route::get('/activity-logs', [ActivityLogController::class, 'index'])->name('activity-logs');
        Route::get('/activity-logs/export', [ActivityLogController::class, 'export'])->middleware('cms.role:superadmin')->name('activity-logs.export');
        Route::get('/users', [UserController::class, 'index'])->name('users.index');
        Route::get('/users/create', [UserController::class, 'create'])->name('users.create');
        Route::get('/users/export', [UserController::class, 'export'])->middleware('cms.role:superadmin')->name('users.export');
        Route::post('/users', [UserController::class, 'store'])->name('users.store');
        Route::get('/users/{user}/edit', [UserController::class, 'edit'])->name('users.edit');
        Route::put('/users/{user}', [UserController::class, 'update'])->name('users.update');
        Route::delete('/users/{user}', [UserController::class, 'destroy'])->name('users.destroy');
        Route::post('/users/{user}/reset-password', [UserController::class, 'resetPassword'])->name('users.reset-password');
        Route::post('/users/{user}/unlock', [UserController::class, 'unlock'])->name('users.unlock');

        Route::middleware('cms.role:superadmin')->group(function (): void {
            Route::get('/permissions', [PermissionMatrixController::class, 'index'])->name('permissions.index');
            Route::put('/permissions', [PermissionMatrixController::class, 'update'])->name('permissions.update');
        });
    });
});

Route::get('/sitemap.xml', [SitemapController::class, 'sitemap'])->name('sitemap');
Route::get('/robots.txt', [SitemapController::class, 'robots'])->name('robots');
Route::get('/media/{media}/file', [MediaController::class, 'show'])->whereNumber('media')->name('media.stream');

Route::prefix('{locale}')
    ->where(['locale' => 'id|en|zh'])
    ->middleware([SetPublicLanguage::class, LocalizeChinesePublicPage::class])
    ->name('localized.')
    ->group(function (): void {
        Route::get('/', [PublicPageController::class, 'home'])->name('home');
        Route::get('/tentang', [PublicPageController::class, 'about'])->name('about');
        Route::get('/produk', [ProductController::class, 'index'])->name('products');
        Route::get('/produk/semua', [ProductController::class, 'catalog'])->name('products.all');
        Route::get('/produk/{slug}', [PublicPageController::class, 'productDetail'])->name('products.show');
        Route::get('/artikel', [PublicPageController::class, 'articles'])->name('articles');
        Route::get('/berita', [PublicPageController::class, 'news'])->name('news');
        Route::get('/berita/{slug}', [PublicPageController::class, 'activityDetailBySlug'])->name('news.show');
        Route::get('/dokumentasi', [PublicPageController::class, 'documentation'])->name('documentation');
        Route::get('/dokumentasi/kegiatan/{id}', [PublicPageController::class, 'activityDetail'])->whereNumber('id')->name('documentation.activity');
        Route::get('/dokumentasi/{slug}', [PublicPageController::class, 'documentationDetail'])->name('documentation.show');
        Route::get('/karir', [PublicPageController::class, 'careers'])->name('careers');
        Route::get('/karir/{id}', [PublicPageController::class, 'careerDetail'])->whereNumber('id')->name('careers.show');
        Route::get('/kontak', [PublicPageController::class, 'contact'])->name('contact');
    });

Route::redirect('/', '/id', 302)->name('home');
Route::redirect('/tentang', '/id/tentang', 301)->name('about');
Route::redirect('/produk/semua', '/id/produk/semua', 301)->name('products.all');
Route::redirect('/produk', '/id/produk', 301)->name('products');
Route::redirect('/kontak', '/id/kontak', 301)->name('contact');
Route::redirect('/dokumentasi', '/id/dokumentasi', 301)->name('documentation');
Route::redirect('/artikel', '/id/artikel', 301)->name('articles');
Route::redirect('/berita', '/id/berita', 301)->name('news');

Route::redirect('/artikel/tambah', '/admin/content/documentations/create')->name('articles.create');
Route::redirect('/artikel/detail', '/id/dokumentasi')->name('articles.detail');
Route::redirect('/berita/hospital-expo-2026', '/id/berita')->name('news.hospital-expo');
