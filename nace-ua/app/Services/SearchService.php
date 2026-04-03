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

        if (DB::getDriverName() === 'pgsql') {
            return $this->searchPgsql($query, $limit);
        }

        $like = '%' . $query . '%';
        $normalizedCode = preg_replace('/[^a-zA-Z0-9]/', '', $query);
        $normalizedLike = $normalizedCode !== '' ? '%' . $normalizedCode . '%' : null;

        /** @var \Illuminate\Support\Collection<int, array{code:string,title:string,standard:string}> $results */
        $results = DB::query()
            ->fromSub(function ($sub) use ($like, $normalizedLike): void {
                $sub
                    // KVED-2010
                    ->selectRaw(
                        "id, code, title, 'kved' as standard, 1 as priority"
                    )
                    ->from('kved_2010')
                    ->where(function ($q) use ($like, $normalizedLike): void {
                        $q->where('code', 'like', $like)
                            ->orWhere('title', 'like', $like);
                        if ($normalizedLike) {
                            $q->orWhere(DB::raw("REPLACE(REPLACE(code, '.', ''), '-', '')"), 'like', $normalizedLike);
                        }
                    })

                    ->unionAll(
                        // NACE 2.1-UA
                        DB::table('nace_2027')
                            ->selectRaw(
                                "id, code, title, 'nace' as standard, 2 as priority"
                            )
                            ->where(function ($q) use ($like, $normalizedLike): void {
                                $q->where('code', 'like', $like)
                                    ->orWhere('title', 'like', $like);
                                if ($normalizedLike) {
                                    $q->orWhere(DB::raw("REPLACE(REPLACE(code, '.', ''), '-', '')"), 'like', $normalizedLike);
                                }
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
     * @return array<int, array{code:string,title:string,standard:string}>
     */
    private function searchPgsql(string $query, int $limit): array
    {
        $normalizedCode = preg_replace('/[^0-9]/', '', $query);
        $likeQuery = $query . '%';
        $normalizedLike = $normalizedCode !== '' ? $normalizedCode . '%' : null;
        $tsQuery = "plainto_tsquery('simple', ?)";

        $sql = "
            SELECT code, title, standard FROM (
                -- Priority 0: Exact or prefix match by code (e.g. '69.10')
                SELECT code, title, 'kved' AS standard, 0 AS priority, 1.0 AS rank 
                FROM kved_2010 WHERE code ILIKE ?
                UNION ALL
                SELECT code, title, 'nace' AS standard, 0 AS priority, 1.0 AS rank 
                FROM nace_2027 WHERE code ILIKE ?

                UNION ALL
                -- Priority 1: Search by code without dots (e.g. '6910' matches '69.10')
                SELECT code, title, 'kved' AS standard, 1 AS priority, 0.9 AS rank 
                FROM kved_2010 WHERE REPLACE(code, '.', '') ILIKE ?
                UNION ALL
                SELECT code, title, 'nace' AS standard, 1 AS priority, 0.9 AS rank 
                FROM nace_2027 WHERE REPLACE(code, '.', '') ILIKE ?

                UNION ALL
                -- Priority 2: Full Text Search by title and description
                SELECT code, title, 'kved' AS standard, 2 AS priority, ts_rank(search_vector, {$tsQuery}) AS rank 
                FROM kved_2010 WHERE search_vector @@ {$tsQuery}
                UNION ALL
                SELECT code, title, 'nace' AS standard, 2 AS priority, ts_rank(search_vector, {$tsQuery}) AS rank 
                FROM nace_2027 WHERE search_vector @@ {$tsQuery}

                UNION ALL
                -- Priority 3: Tags search (synonyms)
                SELECT n.code, n.title, 'nace' AS standard, 3 AS priority, 0.5 AS rank 
                FROM tags t 
                JOIN nace_2027 n ON n.id = t.nace_id 
                WHERE t.tag ILIKE ?
            ) u
            GROUP BY code, title, standard
            ORDER BY MIN(priority), MAX(rank) DESC, code
            LIMIT {$limit}
        ";

        $params = [
            $likeQuery, $likeQuery,             // Priority 0
            $normalizedLike, $normalizedLike,   // Priority 1
            $query, $query, $query, $query,     // Priority 2 (FTS takes 4 params: 2 for rank, 2 for where)
            '%' . $query . '%'                  // Priority 3
        ];

        // If no normalized code (no digits), we can skip Prio 1 params by passing nulls or adjusting query
        // But the SQL REPLACE(...) ILIKE NULL will just return nothing, so it's safe.

        $rows = DB::select($sql, $params);

        return collect($rows)
            ->map(fn ($row) => [
                'code' => $row->code,
                'title' => $row->title,
                'standard' => $row->standard,
            ])
            ->all();
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

