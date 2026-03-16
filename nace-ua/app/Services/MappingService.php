<?php

namespace App\Services;

use App\Models\Kved2010;
use App\Models\Nace2027;
use App\Models\TransitionMapping;
use Illuminate\Database\Eloquent\Collection;

class MappingService
{
    /**
     * Возвращает все маппинги для заданного КВЕД‑кода.
     *
     * @return array{
     *     kved: Kved2010|null,
     *     mappings: array<int, array{
     *         mapping: TransitionMapping,
     *         nace: Nace2027|null,
     *     }>
     * }
     */
    public function forKvedCode(string $code): array
    {
        /** @var Kved2010|null $kved */
        $kved = Kved2010::where('code', $code)->first();

        if (! $kved) {
            return [
                'kved' => null,
                'mappings' => [],
            ];
        }

        /** @var Collection<int, TransitionMapping> $rows */
        $rows = TransitionMapping::where('old_kved_id', $kved->id)
            ->orderByDesc('view_count')
            ->get();

        $naceIds = $rows->pluck('new_nace_id')->unique()->all();

        /** @var Collection<int, Nace2027> $naceById */
        $naceById = Nace2027::whereIn('id', $naceIds)
            ->get()
            ->keyBy('id');

        $mappings = $rows->map(function (TransitionMapping $mapping) use ($naceById): array {
            return [
                'mapping' => $mapping,
                'nace' => $naceById->get($mapping->new_nace_id),
            ];
        })->all();

        return [
            'kved' => $kved,
            'mappings' => $mappings,
        ];
    }
}

