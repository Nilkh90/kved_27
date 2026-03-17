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
            'breadcrumbs' => $this->getBreadcrumbs($section)
        ]);
    }

    public function division(string $id): View
    {
        $division = \App\Models\Nace2027::where('level', 'DIVISION')->with('parent')->findOrFail($id);
        $groups = $division->children()->orderBy('code')->get();

        return view('pages.catalog.item', [
            'item' => $division,
            'children' => $groups,
            'level' => 'division',
            'childLevel' => 'group',
            'breadcrumbs' => $this->getBreadcrumbs($division)
        ]);
    }

    public function group(string $id): View
    {
        $group = \App\Models\Nace2027::where('level', 'GROUP')->with('parent.parent')->findOrFail($id);
        $classes = $group->children()->orderBy('code')->get();

        return view('pages.catalog.item', [
            'item' => $group,
            'children' => $classes,
            'level' => 'group',
            'childLevel' => 'class',
            'breadcrumbs' => $this->getBreadcrumbs($group)
        ]);
    }

    public function class(string $id): View
    {
        $class = \App\Models\Nace2027::where('level', 'CLASS')->with('parent.parent.parent')->findOrFail($id);

        return view('pages.code-detail', [
            'standard' => 'nace',
            'code' => $class,
            'breadcrumbs' => $this->getBreadcrumbs($class)
        ]);
    }

    private function getBreadcrumbs($item): array
    {
        $breadcrumbs = [];
        $current = $item;
        while ($current) {
            array_unshift($breadcrumbs, [
                'title' => $current->code . ($current->level === 'SECTION' ? ' — ' . $current->title : ''),
                'route' => route('catalog.' . strtolower($current->level), $current->id),
                'active' => $current->id === $item->id
            ]);
            $current = $current->parent;
        }
        return $breadcrumbs;
    }

    public function byStandard(string $standard, Request $request): View
    {
        return view('pages.catalog', [
            'standard' => $standard,
        ]);
    }
}

