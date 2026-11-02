<?php

use App\Http\Controllers\ProductController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
})->name('home');

Route::get('/tentang', function () {
    return view('tentang');
})->name('about');

Route::get('/produk', [ProductController::class, 'index'])->name('products');

Route::get('/kontak', function () {
    return view('kontak');
})->name('contact');

Route::get('/dokumentasi', function () {
    return view('dokumentasi');
})->name('documentation');

Route::get('/artikel', function () {
    return view('media-list', ['type' => 'articles']);
})->name('articles');

Route::get('/berita', function () {
    return view('media-list', ['type' => 'news']);
})->name('news');
