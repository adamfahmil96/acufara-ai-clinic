<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Article;
use App\Models\SiteSetting;

class BlogController extends Controller
{
    public function index()
    {
        $settings = SiteSetting::first();
        // Ambil artikel dengan paginasi
        $articles = Article::latest()->paginate(9);

        return view('blog.index', compact('articles', 'settings'));
    }

    public function show(Article $article)
    {
        $settings = SiteSetting::first();
        return view('blog.show', compact('article', 'settings'));
    }
}
