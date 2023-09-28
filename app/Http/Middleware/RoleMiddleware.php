<?php

namespace App\Http\Middleware;

use App\Helpers\ResponseError;
use App\Traits\ApiResponse;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RoleMiddleware
{
    use ApiResponse;

    /**
     * Handle an incoming request.
     *
     * @param \Illuminate\Http\Request $request
     * @param Closure $next
     * @param $role
     * @return \Illuminate\Http\JsonResponse
     * @throws \Psr\SimpleCache\InvalidArgumentException
     */
    public function handle(Request $request, Closure $next, $role)
    {
        $roles = is_array($role) ? $role : explode('|', $role);

        if (auth('sanctum')->user()->hasAnyRole($roles) || auth('sanctum')->user()->hasRole('admin')) {
            if (!cache()->has('project.status') || cache('project.status')->active != 1){
                return $this->errorResponse('ERROR_403',  trans('errors.' . ResponseError::ERROR_403, [], request()->lang ?? 'en'), Response::HTTP_UNAUTHORIZED);
            }
            return $next($request);
        }
        return $this->errorResponse('ERROR_101',   trans('errors.' . ResponseError::ERROR_101, [], request()->lang), Response::HTTP_FORBIDDEN);
    }
}
