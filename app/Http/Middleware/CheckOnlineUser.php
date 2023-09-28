<?php

namespace App\Http\Middleware;

use App\Helpers\ResponseError;
use App\Traits\ApiResponse;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Symfony\Component\HttpFoundation\Response;

class CheckOnlineUser
{
    use ApiResponse;
    /**
     * Handle an incoming request.
     *
     * @param Request $request
     * @param Closure $next
     * @return mixed
     * @throws \Exception
     */
    public function handle(Request $request, Closure $next)
    {
        // if (!cache()->has('project.status') || cache('project.status')->active != 1){
        //     return $this->errorResponse('ERROR_403',  trans('errors.' . ResponseError::ERROR_403, [], request()->lang ?? 'en'), Response::HTTP_UNAUTHORIZED);
        // }

        // if (auth()->check()) {
        //     $expiredAt = now()->addMinutes(3);
        //     Cache::put('user-online-' . auth()->id(), true, $expiredAt);
        // }
        return $next($request);
    }
}
