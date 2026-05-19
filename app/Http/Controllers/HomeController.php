<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\SiteSetting;
use App\Models\Service;
use App\Models\Article;

class HomeController extends Controller
{
    public function index()
    {
        $settings = SiteSetting::first();
        
        // Ambil 3 layanan secara acak atau yang pertama
        $services = Service::limit(3)->get();
        
        // Ambil 3 artikel terbaru
        $articles = Article::latest()->limit(3)->get();

        return view('welcome', compact('settings', 'services', 'articles'));
    }
}
