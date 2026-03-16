<?php

namespace App\Http\Controllers;

use App\Models\Kved2010;
use App\Models\Nace2027;
use App\Services\MappingService;
use Illuminate\Contracts\View\View;

class CodeController extends Controller
{
    public function show(string $standard, string $code): View
    {
        $standard = strtolower($standard);

        if (! in_array($standard, ['kved', 'nace'], true)) {
            abort(404);
        }

        $model = $standard === 'nace' ? Nace2027::class : Kved2010::class;

        /** @var Kved2010|Nace2027|null $codeModel */
        $codeModel = $model::query()
            ->where('code', $code)
            ->first();

        if (! $codeModel) {
            abort(404);
        }

        $viewData = [
            'standard' => $standard,
            'code' => $codeModel,
        ];

        // Маппинг показываем пока только для KVED → NACE.
        if ($standard === 'kved') {
            $mappingResult = app(MappingService::class)->forKvedCode($codeModel->code);

            if (! empty($mappingResult['mappings'])) {
                $top = $mappingResult['mappings'][0];

                $viewData['mapping'] = $top['mapping'];
                $viewData['oldCode'] = $codeModel;
                $viewData['newCode'] = $top['nace'];
            }
        }

        return view('pages.code-detail', [
            ...$viewData,
        ]);
    }
}

