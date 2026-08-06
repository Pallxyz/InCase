<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ItemScanPollController extends Controller
{
    public function show(Request $request): JsonResponse
    {
        $since = $request->query('since');
        $latest = cache()->get('latest_unregistered_scan');

        if (! $latest) {
            return response()->json(['found' => false]);
        }

        if ($since && $latest['at'] <= $since) {
            return response()->json(['found' => false]);
        }

        return response()->json([
            'found' => true,
            'uid' => $latest['uid'],
            'at' => $latest['at'],
        ]);
    }
}