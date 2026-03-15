<?php

namespace App\Http\Controllers;

use Illuminate\Contracts\View\View;

class CodeController extends Controller
{
    public function show(string $standard, string $code): View
    {
        return view('pages.code-detail', [
            'standard' => $standard,
            'code' => $code,
        ]);
    }
}

