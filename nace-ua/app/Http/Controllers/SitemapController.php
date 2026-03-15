<?php

namespace App\Http\Controllers;

use Illuminate\Http\Response;

class SitemapController extends Controller
{
    public function index(): Response
    {
        return response('<?xml version="1.0" encoding="UTF-8"?><urlset></urlset>', 200)
            ->header('Content-Type', 'application/xml');
    }
}

