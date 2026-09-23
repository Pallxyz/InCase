<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RoleMiddleware
{
    /**
     * Handle an incoming request.
     *
     * Bisa dipakai dengan satu role ('role:teacher') atau beberapa role
     * dipisah koma ('role:teacher,admin').
     */
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        if (! $request->user()) {
            abort(403);
        }

        if (! in_array($request->user()->role, $roles, true)) {
            abort(403);
        }

        return $next($request);
    }
}