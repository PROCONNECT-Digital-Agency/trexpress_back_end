<?php

namespace App\Http\Controllers\API\v1\Auth;

use App\Helpers\ResponseError;
use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\AuthService\AuthByEmail;
use App\Services\AuthService\AuthByMobilePhone;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Twilio\Exceptions\ConfigurationException;

class RegisterController extends Controller
{
    use ApiResponse;

    /**
     * @throws ConfigurationException
     */
    public function register(Request $request)
    {
        if (isset($request->phone)) {
            $user = User::where('phone', $request->phone)->where('phone_verified_at', '!=', null)->where('active',1)->first();
            if ($user) {
                return $this->errorResponse(
                    ResponseError::ERROR_106, trans('errors.' . ResponseError::ERROR_106, [], request()->lang ?? config('app.locale')),
                    Response::HTTP_BAD_REQUEST
                );
            }
            return (new AuthByMobilePhone())->authentication($request->all());
        } elseif (isset($request->email)) {
            $user = User::where('email', $request->email)->where('email_verified_at', '!=', null)->where('active',1)->first();
            if ($user) {
                return $this->errorResponse(
                    ResponseError::ERROR_106, trans('errors.' . ResponseError::ERROR_106, [], request()->lang ?? config('app.locale')),
                    Response::HTTP_BAD_REQUEST
                );
            }
            return (new AuthByEmail())->authentication($request->all());
        }
        return $this->errorResponse(ResponseError::ERROR_400, 'errors.' . ResponseError::ERROR_400, Response::HTTP_BAD_REQUEST);
    }
}
