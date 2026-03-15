<?php

namespace App\Http\Controllers;

use Illuminate\Contracts\View\View;

class InfoController extends Controller
{
    public function index(): View
    {
        return view('pages.info');
    }

    public function article(string $slug): View
    {
        return view('pages.info', [
            'slug' => $slug,
        ]);
    }
}

