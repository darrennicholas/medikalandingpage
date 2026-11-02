<?php

namespace App\Http\Controllers;

use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\File;

class ProductController extends Controller
{
    public function index(): View
    {
        $categories = json_decode(File::get(resource_path('products.json')), true, 512, JSON_THROW_ON_ERROR);

        return view('produk', ['categories' => $categories]);
    }
}
