<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CustomerMiddleware
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Check if user is authenticated
        if (!$request->user()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthenticated'
            ], 401);
        }

        // Check if user is customer
        if ($request->user()->role !== 'customer') {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized. Customer access required.'
            ], 403);
        }

        return $next($request);
    }
}