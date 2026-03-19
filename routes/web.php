<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\V1\SitemapController;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/sitemap.xml', [SitemapController::class, 'index']);
