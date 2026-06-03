<?php

namespace App\Http\Controllers\Api\V1;

use App\Models\News;
use Illuminate\Http\Request;

class FeedController extends BaseController
{
    public function rss()
    {
        $news = News::published()->latest()->limit(20)->get();
        return response()->view('feeds.rss', ['news' => $news])->header('Content-Type', 'text/xml');
    }

    public function sitemap()
    {
        $news = News::published()->latest()->limit(1000)->get();
        return response()->view('feeds.sitemap', ['news' => $news])->header('Content-Type', 'text/xml');
    }
}
