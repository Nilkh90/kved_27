<?php

namespace App\Services;

use App\Models\Kved2010;
use App\Models\Nace2027;

class ClassifierService
{
    /**
     * Возвращает дерево верхнего рівня для заданого стандарту.
     *
     * @return array<int, array{id:string,code:string,title:string,children:array<int,array{id:string,code:string,title:string}>}>
     */
    public function getRootNodes(string $standard): array
    {
        $model = $standard === 'nace' ? Nace2027::class : Kved2010::class;

        $roots = $model::query()
            ->whereNull('parent_id')
            ->orderBy('code')
            ->get();

        return $roots->map(function ($node): array {
            return [
                'id' => (string) $node->id,
                'code' => $node->code,
                'title' => $node->title,
                'children' => $this->getChildren($node),
            ];
        })->all();
    }

    /**
     * Возвращает дочерние узлы для конкретного узла.
     *
     * @return array<int, array{id:string,code:string,title:string}>
     */
    public function getChildren(object $node): array
    {
        $children = $node->newQuery()
            ->where('parent_id', $node->id)
            ->orderBy('code')
            ->get();

        return $children->map(function ($child): array {
            return [
                'id' => (string) $child->id,
                'code' => $child->code,
                'title' => $child->title,
            ];
        })->all();
    }
}

