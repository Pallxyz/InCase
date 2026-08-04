<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\ScanService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ScanController extends Controller
{
    public function __construct(private ScanService $scanService) {}

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'rfid_uid' => ['required', 'string'],
        ]);

        $result = $this->scanService->handle($validated['rfid_uid']);

        return response()->json($result['body'], $result['code']);
    }
}
