<?php

namespace App\Http\Controllers;

use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use App\Models\Kved2010;
use App\Models\Nace2027;

class CatalogController extends Controller
{
    private function getModel(string $standard): string
    {
        return $standard === 'nace' ? Nace2027::class : Kved2010::class;
    }

    public function index(string $standard): View
    {
        $model = $this->getModel($standard);
        $sections = $model::where('level', 'SECTION')->orderBy('code')->get();

        return view('pages.catalog', [
            'standard' => $standard,
            'sections' => $sections,
        ]);
    }

    public function section(string $standard, string $code): View
    {
        $model = $this->getModel($standard);
        $code = strtoupper($code);
        $section = $model::where('level', 'SECTION')->where('code', $code)->firstOrFail();
        $divisions = $section->children()->orderBy('code')->get();

        return view('pages.catalog.item', [
            'standard' => $standard,
            'item' => $section,
            'children' => $divisions,
            'level' => 'section',
            'childLevel' => 'division',
            'breadcrumbs' => $this->getBreadcrumbs($section, $standard)
        ]);
    }

    public function division(string $standard, string $division_code): View
    {
        $model = $this->getModel($standard);
        $division = $model::where('level', 'DIVISION')
            ->where('code', $division_code)
            ->with('parent')
            ->firstOrFail();

        $groups = $division->children()->orderBy('code')->get();

        return view('pages.catalog.item', [
            'standard' => $standard,
            'item' => $division,
            'children' => $groups,
            'level' => 'division',
            'childLevel' => 'group',
            'breadcrumbs' => $this->getBreadcrumbs($division, $standard)
        ]);
    }

    public function group(string $standard, string $division_code, string $group_code): View
    {
        $model = $this->getModel($standard);
        $group_code_normalized = str_replace('-', '.', $group_code);
        
        $group = $model::where('level', 'GROUP')
            ->where('code', $group_code_normalized)
            ->with('parent')
            ->firstOrFail();

        // Hierarchy validation
        if ($group->parent->code !== $division_code) {
            abort(404);
        }

        $classes = $group->children()->orderBy('code')->get();

        return view('pages.catalog.item', [
            'standard' => $standard,
            'item' => $group,
            'children' => $classes,
            'level' => 'group',
            'childLevel' => 'class',
            'breadcrumbs' => $this->getBreadcrumbs($group, $standard)
        ]);
    }

    public function class(string $standard, string $division_code, string $group_code, string $class_code): View
    {
        $model = $this->getModel($standard);
        $group_code_normalized = str_replace('-', '.', $group_code);
        $class_code_normalized = str_replace('-', '.', $class_code);

        $class = $model::where('level', 'CLASS')
            ->where('code', $class_code_normalized)
            ->with('parent.parent')
            ->firstOrFail();

        // Hierarchy validation
        if ($class->parent->code !== $group_code_normalized || $class->parent->parent->code !== $division_code) {
            abort(404);
        }

        return view('pages.code-detail', [
            'standard' => $standard,
            'code' => $class,
            'breadcrumbs' => $this->getBreadcrumbs($class, $standard)
        ]);
    }

    private function getBreadcrumbs($item, string $standard): array
    {
        $breadcrumbs = [];
        $current = $item;
        while ($current) {
            $params = ['standard' => $standard];
            $lvl = strtolower($current->level);
            
            if ($lvl === 'section') {
                $params['code'] = strtolower($current->code);
            } elseif ($lvl === 'division') {
                $params['division_code'] = $current->code;
            } elseif ($lvl === 'group') {
                $params['division_code'] = $current->parent->code;
                $params['group_code'] = $current->slug;
            } elseif ($lvl === 'class') {
                $params['division_code'] = $current->parent->parent->code;
                $params['group_code'] = $current->parent->slug;
                $params['class_code'] = $current->slug;
            }

            array_unshift($breadcrumbs, [
                'title' => $current->code . ($current->level === 'SECTION' ? ' — ' . $current->title : ''),
                'route' => route('catalog.' . ($lvl === 'section' ? 'section' : $lvl), $params),
                'active' => $current->id === $item->id
            ]);
            $current = $current->parent;
        }

        array_unshift($breadcrumbs, [
            'title' => 'Каталог',
            'route' => route('catalog.index', ['standard' => $standard]),
            'active' => false
        ]);

        return $breadcrumbs;
    }
}
