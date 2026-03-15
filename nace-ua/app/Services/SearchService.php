<?php

namespace App\Services;

class SearchService
{
    public function search(string $query): array
    {
        return [];
    }

    /**
     * Временная заглушка для подсказок поиска.
     *
     * @return array<int, array{code:string,title:string,standard:string}>
     */
    public function suggest(string $query, int $limit = 8): array
    {
        $query = trim($query);
        if ($query === '') {
            return [];
        }

        // TODO: заменить на реальный поиск по БД
        return collect(range(1, $limit))
            ->map(function (int $i) use ($query) {
                return [
                    'code' => sprintf('%02d.%02d', $i, $i),
                    'title' => "Тестовий результат {$i} для «{$query}»",
                    'standard' => 'kved',
                ];
            })
            ->all();
    }
}

