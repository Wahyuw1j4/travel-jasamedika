<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AuthToken
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user(); // otomatis dari Sanctum

        if (!$user) {
            return response()->json([
                'message' => 'Unauthenticated.'
            ], 401);
        }

        return $next($request);
    }
}
