<?php

namespace App\Http\Controllers\API\v1\Rest;

use App\Helpers\ResponseError;
use App\Http\Controllers\Controller;
use App\Services\ProjectService\ProjectService;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ProjectController extends RestBaseController
{



    public function licenceCheck(Request $request)
    {
        $response = (new ProjectService())->activationKeyCheck();
        $response = json_decode($response);

        if ($response->key == config('credential.purchase_code') && $response->active) {
            return $this->successResponse(trans('errors.' .ResponseError::NO_ERROR, [], \request()->lang ?? 'en'),  $response);
        }
        return $this->errorResponse(ResponseError::ERROR_403, __('errors.ERROR_403'),  Response::HTTP_FORBIDDEN);
    }
}
