<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;

class AdminController extends Controller
{
    public function dashboard(): View
    {
        $stats = [
            'kved_count' => DB::table('kved_2010')->count(),
            'nace_count' => DB::table('nace_2027')->count(),
            'mapping_count' => DB::table('transition_mapping')->count(),
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

    /**
     * Run the KVED-2010 import artisan command.
     * Triggered by POST /admin/run-import-kved
     */
    public function runImportKved(Request $request): JsonResponse
    {
        $fresh = $request->boolean('fresh', false);
        $options = $fresh ? ['--fresh' => true] : [];

        try {
            Artisan::call('kved:import', $options);
            $output = Artisan::output();

            return response()->json([
                'success' => true,
                'output' => $output,
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}


