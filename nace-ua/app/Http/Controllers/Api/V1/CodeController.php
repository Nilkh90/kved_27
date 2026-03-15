<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;

class CodeController extends Controller
{
    public function show(string $id): JsonResponse
    {
        return response()->json([
            'data' => [
                'id' => $id,
            ],
        ]);
    }
}

