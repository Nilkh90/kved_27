<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Services\SearchService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SearchController extends Controller
{
    public function __construct(
        protected SearchService $searchService
    ) {
    }

    public function index(Request $request): JsonResponse
    {
        $query = (string) $request->input('q', '');

        return response()->json([
            'data' => $query === ''
                ? []
                : $this->searchService->search($query),
        ]);
    }
}

