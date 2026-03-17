<?php

namespace App\Http\Controllers;

use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class CatalogController extends Controller
{
    public function index(): View
    {
        $sections = \App\Models\Nace2027::where('level', 'SECTION')->orderBy('code')->get();

        return view('pages.catalog', [
            'standard' => 'nace',
            'sections' => $sections,
        ]);
    }

    public function section(string $id): View
    {
        $section = \App\Models\Nace2027::where('level', 'SECTION')->findOrFail($id);
        $divisions = $section->children()->orderBy('code')->get();

        return view('pages.catalog.item', [
            'item' => $section,
            'children' => $divisions,
            'level' => 'section',
            'childLevel' => 'division',
        ]);
    }

    public function division(string $id): View
    {
        $division = \App\Models\Nace2027::where('level', 'DIVISION')->findOrFail($id);
        $groups = $division->children()->orderBy('code')->get();

        return view('pages.catalog.item', [
            'item' => $division,
            'children' => $groups,
            'level' => 'division',
            'childLevel' => 'group',
        ]);
    }

    public function group(string $id): View
    {
        $group = \App\Models\Nace2027::where('level', 'GROUP')->findOrFail($id);
        $classes = $group->children()->orderBy('code')->get();

        return view('pages.catalog.item', [
            'item' => $group,
            'children' => $classes,
            'level' => 'group',
            'childLevel' => 'class',
        ]);
    }

    public function class(string $id): View
    {
        $class = \App\Models\Nace2027::where('level', 'CLASS')->findOrFail($id);

        return view('pages.code-detail', [
            'standard' => 'nace',
            'code' => $class,
        ]);
    }

    public function byStandard(string $standard, Request $request): View
    {
        return view('pages.catalog', [
            'standard' => $standard,
        ]);
    }
}

