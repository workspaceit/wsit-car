<?php

namespace App\Http\Middleware\Api\Role;

use App\Models\User;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;

class Admin
{
    /**
     * Handle an incoming request.
     *
     * @param  Request $request
     * @param  Closure $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        if (Auth::guard('api')->user()->type !== User::TYPE_ADMIN) {
            return response()->json([
                'status' => 'error',
                'message' => 'You are not allow to do that.',
                'data' => []
            ], Response::HTTP_FORBIDDEN);
        }

        return $next($request);
    }
}
