<?php

namespace App\Services\AuthService;

use App\Helpers\ResponseError;
use App\Http\Resources\UserResource;
use App\Models\User;
use App\Repositories\UserRepository\UserRepository;
use App\Services\CoreService;
use App\Services\UserServices\UserWalletService;
use Illuminate\Auth\Events\Registered;
use Symfony\Component\HttpFoundation\Response;

class AuthByEmail extends CoreService
{

    /**
     * @return mixed
     */
    protected function getModelClass()
    {
        return User::class;
    }

    public function authentication(array $array)
    {
        $user = $this->model()->firstWhere('email', $array['email']);

        if ($user) {
            return $this->errorResponse(ResponseError::ERROR_106, trans('errors.'. ResponseError::ERROR_106, [], request()->lang ?? 'en'), Response::HTTP_BAD_REQUEST);
        } else {
            $user = $this->model()->create([
                'firstname' => $array['firstname'],
                'email' => $array['email'],
                'phone' => '',
                'password' => bcrypt($array['password']),
                'ip_address' => request()->ip(),
                'email_verified_at' => now(),
                'active' => 0
            ]);

            // event(new Registered($user));

            if(!isset($user->wallet)){
                (new UserWalletService())->create($user);
            }

            $token = $user->createToken('api_token')->plainTextToken;

            return $this->successResponse('User successfully login', [
                'token' => $token,
                'user' => UserResource::make($user),
            ]);
        }

    }

}
