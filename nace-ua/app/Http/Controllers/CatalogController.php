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

    public function section(string $code): View
    {
        $code = strtoupper($code);
        $section = \App\Models\Nace2027::where('level', 'SECTION')->where('code', $code)->firstOrFail();
        $divisions = $section->children()->orderBy('code')->get();

        return view('pages.catalog.item', [
            'item' => $section,
            'children' => $divisions,
            'level' => 'section',
            'childLevel' => 'division',
            'breadcrumbs' => $this->getBreadcrumbs($section)
        ]);
    }

    public function division(string $division_code): View
    {
        $division = \App\Models\Nace2027::where('level', 'DIVISION')
            ->where('code', $division_code)
            ->with('parent')
            ->firstOrFail();

        $groups = $division->children()->orderBy('code')->get();

        return view('pages.catalog.item', [
            'item' => $division,
            'children' => $groups,
            'level' => 'division',
            'childLevel' => 'group',
            'breadcrumbs' => $this->getBreadcrumbs($division)
        ]);
    }

    public function group(string $division_code, string $group_code): View
    {
        $group_code_normalized = str_replace('-', '.', $group_code);
        
        $group = \App\Models\Nace2027::where('level', 'GROUP')
            ->where('code', $group_code_normalized)
            ->with('parent')
            ->firstOrFail();

        // Hierarchy validation
        if ($group->parent->code !== $division_code) {
            abort(404);
        }

        $classes = $group->children()->orderBy('code')->get();

        return view('pages.catalog.item', [
            'item' => $group,
            'children' => $classes,
            'level' => 'group',
            'childLevel' => 'class',
            'breadcrumbs' => $this->getBreadcrumbs($group)
        ]);
    }

    public function class(string $division_code, string $group_code, string $class_code): View
    {
        $group_code_normalized = str_replace('-', '.', $group_code);
        $class_code_normalized = str_replace('-', '.', $class_code);

        $class = \App\Models\Nace2027::where('level', 'CLASS')
            ->where('code', $class_code_normalized)
            ->with('parent.parent')
            ->firstOrFail();

        // Hierarchy validation
        if ($class->parent->code !== $group_code_normalized || $class->parent->parent->code !== $division_code) {
            abort(404);
        }

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
            $params = [];
            $lvl = strtolower($current->level);
            
            if ($lvl === 'section') {
                $params = ['code' => strtolower($current->code)];
            } elseif ($lvl === 'division') {
                $params = ['division_code' => $current->code];
            } elseif ($lvl === 'group') {
                $params = [
                    'division_code' => $current->parent->code,
                    'group_code' => $current->slug
                ];
            } elseif ($lvl === 'class') {
                $params = [
                    'division_code' => $current->parent->parent->code,
                    'group_code' => $current->parent->slug,
                    'class_code' => $current->slug
                ];
            }

            array_unshift($breadcrumbs, [
                'title' => $current->code . ($current->level === 'SECTION' ? ' — ' . $current->title : ''),
                'route' => route('catalog.' . $lvl, $params),
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

