<?php

namespace Kaely\AuthPackage\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

class CheckPermission
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, string $permission): Response
    {
        if (!$request->user()) {
            return response()->json(['message' => 'Unauthenticated.'], 401);
        }

        if (!$request->user()->hasPermission($permission)) {
            return response()->json([
                'message' => 'Access denied. Insufficient permissions.',
                'required_permission' => $permission
            ], 403);
        }

        return $next($request);
    }
} 