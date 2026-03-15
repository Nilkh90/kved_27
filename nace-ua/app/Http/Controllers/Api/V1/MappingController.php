<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;

class MappingController extends Controller
{
    public function show(string $kvedId): JsonResponse
    {
        return response()->json([
            'data' => [
                'old_kved_id' => $kvedId,
                'mappings' => [],
            ],
        ]);
    }
}

