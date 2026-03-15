<?php

namespace App\Http\Controllers;

use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class CatalogController extends Controller
{
    public function index(): View
    {
        return view('pages.catalog', [
            'standard' => 'kved',
        ]);
    }

    public function byStandard(string $standard, Request $request): View
    {
        return view('pages.catalog', [
            'standard' => $standard,
        ]);
    }
}

