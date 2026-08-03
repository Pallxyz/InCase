<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureValidApiKey
{
    public function handle(Request $request, Closure $next): Response
    {
        $key = $request->header('X-API-KEY');

        if (! $key || $key !== config('services.esp32.api_key')) {
            return response()->json(['message' => 'Unauthorized. API key tidak valid.'], 401);
        }

        return $next($request);
    }
}