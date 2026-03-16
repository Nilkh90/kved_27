<?php

namespace App\Services;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class SearchService
{
    /**
     * Базовый поиск по БД: KVED-2010, NACE 2.1-UA и тегам.
     *
     * @return array<int, array{code:string,title:string,standard:string}>
     */
    public function search(string $query, int $limit = 20): array
    {
        $query = trim($query);
        if ($query === '') {
            return [];
        }

        $like = '%' . $query . '%';

        /** @var \Illuminate\Support\Collection<int, array{code:string,title:string,standard:string}> $results */
        $results = DB::query()
            ->fromSub(function ($sub): void use ($like) {
                $sub
                    // KVED-2010
                    ->selectRaw(
                        "id, code, title, 'kved' as standard, 1 as priority"
                    )
                    ->from('kved_2010')
                    ->where(function ($q) use ($like): void {
                        $q->where('code', 'like', $like)
                            ->orWhere('title', 'like', $like);
                    })

                    ->unionAll(
                        // NACE 2.1-UA
                        DB::table('nace_2027')
                            ->selectRaw(
                                "id, code, title, 'nace' as standard, 2 as priority"
                            )
                            ->where(function ($q) use ($like): void {
                                $q->where('code', 'like', $like)
                                    ->orWhere('title', 'like', $like);
                            })
                    )

                    ->unionAll(
                        // Теги (синонимы) → NACE
                        DB::table('tags')
                            ->join('nace_2027', 'tags.nace_id', '=', 'nace_2027.id')
                            ->selectRaw(
                                'nace_2027.id, nace_2027.code, nace_2027.title, \'nace\' as standard, 3 as priority'
                            )
                            ->where('tags.tag', 'like', $like)
                    );
            }, 'u')
            ->orderBy('priority')
            ->orderBy('code')
            ->limit($limit)
            ->get()
            ->map(function ($row): array {
                return [
                    'code' => $row->code,
                    'title' => $row->title,
                    'standard' => $row->standard,
                ];
            });

        return $results->all();
    }

    /**
     * Временная заглушка для подсказок поиска.
     *
     * @return array<int, array{code:string,title:string,standard:string}>
     */
    public function suggest(string $query, int $limit = 8): array
    {
        return $this->search($query, $limit);
    }
}

