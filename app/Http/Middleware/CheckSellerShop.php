<?php

namespace App\Http\Middleware;

use App\Helpers\ResponseError;
use App\Traits\ApiResponse;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckSellerShop
{
    use ApiResponse;

    /**
     * Handle an incoming request.
     *
     * @param \Illuminate\Http\Request $request
     * @param \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\JsonResponse
     * @throws \Exception
     */
    public function handle(Request $request, Closure $next)
    {
        if (!cache()->has('project.status') || cache('project.status')->active != 1){
            return $this->errorResponse('ERROR_403',  trans('errors.' . ResponseError::ERROR_403, [], request()->lang ?? 'en'), Response::HTTP_UNAUTHORIZED);
        }

        if (auth('sanctum')->user()->hasRole(['seller','moderator']))
        {
            return $next($request);
        }
        return $this->errorResponse(
            ResponseError::ERROR_204,
            trans('errors.' . ResponseError::ERROR_204, [], $request->lang),
            Response::HTTP_NOT_FOUND);
    }
}
