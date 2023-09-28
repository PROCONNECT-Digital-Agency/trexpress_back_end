<?php

namespace App\Http\Controllers\API\v1\Rest;

use App\Helpers\ResponseError;
use App\Http\Resources\UserResource;
use App\Models\User;
use App\Repositories\ProjectRepository\ProjectRepository;
use App\Services\AuthService\UserVerifyService;
use App\Services\CurrencyServices\CurrencyService;
use App\Services\LanguageServices\LanguageService;
use App\Services\ProjectService\ProjectService;
use App\Services\UserServices\UserService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;

class InstallController extends RestBaseController
{

    public function __construct()
    {
    }

    public function checkInitFile()
    {
        $result = File::exists(config_path('init.php'));
        if ($result){
            $init = config('init');
            return $this->successResponse(trans('errors.' .ResponseError::NO_ERROR, [], \request()->lang ?? 'en'), $init);
        }
        return $this->errorResponse(ResponseError::ERROR_404, trans('errors.' .ResponseError::ERROR_404, [], \request()->lang ?? 'en'), Response::HTTP_NOT_FOUND);

    }

    public function setInitFile(Request $request)
    {
        $name = $request->name ?? env('APP_NAME');
        $name = Str::of($name)->replace("'", "\'");

        $favicon = $request->favicon ?? '';
        $logo = $request->logo ?? '';
        $delivery = $request->delivery ?? 0;
        $multyshop = $request->multy_shop ?? 0;

        File::put(config_path('init.php') ,
            "<?php\n return [
                        \n'name' => '$name',
                        \n'favicon' => '$favicon',
                        \n'logo' => '$logo',
                        \n'delivery' => '$delivery',
                        \n'shop_type' => '$multyshop',
                        \n];");
            $result = config('init');

            return $this->successResponse(trans('errors.' .ResponseError::NO_ERROR, [], \request()->lang ?? 'en'), $result);
    }

    public function setDatabase(Request $request)
    {
        $path = base_path('.env');
        $env = $request->env ? 'production' : 'local';
        $database = $request->database ?? 'laravel';
        $username = $request->username ?? 'root';
        $appName = config('init.name') ?? 'Laravel';

        file_put_contents($path, str_replace(
            'APP_NAME' . '=' . env('APP_NAME'), 'APP_NAME' . '=' . Str::slug($appName), file_get_contents($path)
        ));
        file_put_contents($path, str_replace(
            'APP_ENV' . '='  . env('APP_ENV'), 'APP_ENV' . '=' . $env, file_get_contents($path),
        ));
        file_put_contents($path, str_replace(
            'DB_DATABASE' . '=' . env('DB_DATABASE'), 'DB_DATABASE' . '=' . $database, file_get_contents($path)
        ));
        file_put_contents($path, str_replace(
            'DB_USERNAME' . '='  . env('DB_USERNAME'), 'DB_USERNAME' . '=' . $username, file_get_contents($path)
        ));
        file_put_contents($path, str_replace(
            'DB_PASSWORD' . '='  . env('DB_PASSWORD'), 'DB_PASSWORD' . '=' . $request->password ?? '', file_get_contents($path)
        ));

        Artisan::call('config:clear');
        try {
            DB::connection()->getPdo();
            return $this->successResponse(trans('errors.' .ResponseError::NO_ERROR, [], \request()->lang ?? 'en'), true);
        } catch (\Exception $exception) {
            return $this->errorResponse(ResponseError::ERROR_415, $exception->getMessage(), Response::HTTP_BAD_REQUEST);
        }

    }


    public function migrationRun()
    {
        $result = Artisan::call('migrate:fresh --seed --force');
        if (!$result) {
            return $this->successResponse(trans('errors.' .ResponseError::NO_ERROR, [], \request()->lang ?? 'en'),  $result);
        }
        return $this->errorResponse(ResponseError::ERROR_501, trans('errors.' .ResponseError::ERROR_501, [], \request()->lang ?? 'en'), Response::HTTP_BAD_REQUEST);
    }

    public function createAdmin(Request $request)
    {
        $admin = User::orderBy('id')->first();
        if ($admin){
            return $this->errorResponse(ResponseError::ERROR_506, trans('errors.' .ResponseError::ERROR_506, [], \request()->lang ?? 'en'), Response::HTTP_BAD_REQUEST);
        }

        $result = (new UserService())->create($request);
        if ($result['status']){
            $result['data']->syncRoles('admin');
            (new UserVerifyService())->verifyEmail($result['data']);

            $token = $result['data']->createToken('api_token')->plainTextToken;

            return $this->successResponse('User successfully login', [
                'access_token' => $token,
                'token_type' => 'Bearer',
                'user' => UserResource::make($result['data']),
            ]);
        }
        return $this->errorResponse(ResponseError::ERROR_501, trans('errors.' .ResponseError::ERROR_501, [], \request()->lang ?? 'en'), Response::HTTP_BAD_REQUEST);
    }

//    public function createCurrency(Request $request)
//    {
//        $result = (new CurrencyService())->create($request);
//        if ($result['status']){
//            return $this->successResponse(trans('errors.' .ResponseError::NO_ERROR, [], \request()->lang ?? 'en'),  $result['data']);
//        }
//        return $this->errorResponse(ResponseError::ERROR_501, trans('errors.' .ResponseError::ERROR_501, [], \request()->lang ?? 'en'), Response::HTTP_BAD_REQUEST);
//    }

//    public function createLanguage(Request $request)
//    {
//        $result = (new LanguageService())->create($request);
//        if ($result['status']){
//            return $this->successResponse(trans('errors.' .ResponseError::NO_ERROR, [], \request()->lang ?? 'en'),  $result['data']);
//        }
//        return $this->errorResponse(ResponseError::ERROR_501, trans('errors.' .ResponseError::ERROR_501, [], \request()->lang ?? 'en'), Response::HTTP_BAD_REQUEST);
//    }

    public function licenceCredentials(Request $request){
        File::put(config_path('credential.php') ,
            "<?php\n return [
                        \n'purchase_id' => '$request->purchase_id',
                            \n'purchase_code' => '$request->purchase_code',
                        \n];");

        $response = (new ProjectService())->activationKeyCheck();
        $response = json_decode($response);
        if ($response->key == config('credential.purchase_code') && $response->active) {
            return $this->successResponse(trans('errors.' .ResponseError::NO_ERROR, [], \request()->lang ?? 'en'),  $response);
        }
        return $this->errorResponse(ResponseError::ERROR_403, __('errors.ERROR_403'),  Response::HTTP_FORBIDDEN);
    }

}
