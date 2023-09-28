<?php

namespace App\Http\Controllers\API\v1\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\AfterVerifyRequest;
use App\Http\Resources\UserResource;
use App\Models\Notification;
use App\Models\User;
use App\Services\AuthService\AuthByEmail;
use App\Services\AuthService\AuthByMobilePhone;
use App\Services\UserServices\UserWalletService;
use App\Traits\ApiResponse;
use Illuminate\Auth\Events\Verified;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class VerifyAuthController extends Controller
{
    use ApiResponse;

//    public function verifyEmail(Request $request): \Illuminate\Http\JsonResponse
//    {
//        return (new AuthByEmail())->confirmOPTCode($request->all());
//    }

    public function verifyPhone(Request $request)
    {
        return (new AuthByMobilePhone())->confirmOPTCode($request->all());
    }


    public function verifyEmail(Request $request): \Illuminate\Http\JsonResponse
    {
        $user = User::find($request->route('id'));

        if ($user->hasVerifiedEmail()) {
            return $this->successResponse('Email already verified', [
                'email' => $user->email,
            ]);
        }

        if ($user->markEmailAsVerified()) {
            event(new Verified($user));
        }

        return $this->successResponse('Email successfully verified', [
            'email' => $user->email,
        ]);
    }

    public function afterVerifyEmail(AfterVerifyRequest $request): JsonResponse
    {
        $user = User::where('phone', $request->input('phone'))->first();

        if (empty($user)) {
            $user = User::withTrashed()
                ->updateOrCreate([
                    'phone'             => $request->input('phone')
                ], [
                    'phone'             => $request->input('phone'),
                    'active'            => 1,
                    'phone_verified_at' => now(),
                    'deleted_at'        => null,
                    'firstname'         => $request->input('firstname'),
                    'lastname'          => $request->input('lastname'),
                    'gender'            => $request->input( 'gender'),
                    'password'          => bcrypt($request->input('password', 'password')),
                ]);

        }

        $user->syncRoles('user');
//
//        $referral = User::where('my_referral', $request->input('referral', $user->referral))
//            ->first();

//        if (!empty($referral) && !empty($referral->firebase_token)) {
//            $this->sendNotification(
//                [$referral->firebase_token],
//                "By your referral registered new user. $user->name_or_email",
//                "Congratulations!",
//                [
//                    'type' => 'new_user_by_referral'
//                ],
//            );
//        }
//
//
//        $user->notifications()->sync(
//            \App\Models\Notification::where('type', \App\Models\Notification::PUSH)
//                ->select(['id', 'type'])
//                ->first()
//                ->pluck('id')
//                ->toArray()
//        );

//        $id = \App\Models\Notification::where('type', \App\Models\Notification::PUSH)->select(['id', 'type'])->first()?->id;

//        if ($id) {
//            $user->notifications()->sync([$id]);
//        } else {
//            $user->notifications()->forceDelete();
//        }

//        $user->emailSubscription()->updateOrCreate([
//            'user_id' => $user->id
//        ], [
//            'active' => true
//        ]);

        if(empty($user->wallet)) {
            (new UserWalletService())->create($user);
        }

        $token = $user->createToken('api_token')->plainTextToken;

        return $this->successResponse(__('web.user_successfully_registered'), [
            'access_token' => $token,
            'user'  => UserResource::make($user->load('wallet')),
        ]);
    }

}
