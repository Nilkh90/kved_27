<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Contracts\View\View;

class AdminController extends Controller
{
    public function dashboard(): View
    {
        $stats = [
            'kved_count' => \Illuminate\Support\Facades\DB::table('kved_2010')->count(),
            'nace_count' => \Illuminate\Support\Facades\DB::table('nace_2027')->count(),
            'mapping_count' => \Illuminate\Support\Facades\DB::table('transition_mapping')->count(),
        ];

        return view('admin.dashboard', compact('stats'));
    }

    public function import(): View
    {
        return view('admin.import');
    }

    public function kved(): View
    {
        return view('admin.kved');
    }

    public function nace(): View
    {
        return view('admin.nace');
    }

    public function mappings(): View
    {
        return view('admin.mappings');
    }
}

