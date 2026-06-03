<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\V1\FeedController;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/sitemap.xml', [FeedController::class, 'sitemap']);
Route::get('/robots.txt', [FeedController::class, 'robots']);
Route::get('/llms.txt', [FeedController::class, 'llms']);
Route::get('/feeds/rss', [FeedController::class, 'rss']);
Route::get('/feeds/sitemap.xml', [FeedController::class, 'sitemap']);
