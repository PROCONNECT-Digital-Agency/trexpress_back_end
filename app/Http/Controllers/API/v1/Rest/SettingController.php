<?php

namespace App\Http\Controllers\API\v1\Rest;

use App\Helpers\ResponseError;
use App\Http\Controllers\Controller;
use App\Models\Payment;
use App\Models\Settings;
use App\Models\Translation;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class SettingController extends Controller
{
use ApiResponse;
    public function settingsInfo()
    {
        $settings = Settings::adminSettings();
        return $this->successResponse(trans('web.list_of_settings', [], \request()->lang), $settings);
    }

    public function translationsPaginate(Request $request)
    {
        $lang = $request->lang ?? 'en';
        $translations = Cache::remember('language-'. $lang, 86400, function () use($lang) {
            return Translation::where('locale', $lang)->where('status', 1)->pluck('value', 'key');
        });

        return $this->successResponse('errors.' . ResponseError::NO_ERROR, $translations->all());
    }

    public function systemInformation()
    {
        return Cache::remember('server-info', 84600, function (){
            // get MySql version from DataBase
            $mysql = DB::selectOne( DB::raw('SHOW VARIABLES LIKE "%innodb_version%"'));

            return $this->successResponse("success", [
                'PHP Version' => phpversion(),
                'Laravel Version' => app()->version(),
                'OS Version' => php_uname(),
                'MySql Version' => $mysql->Value,
                'NodeJs Version' =>  exec('node -v'),
                'NPM Version' => exec('npm -v'),
                'Composer Version' => exec('composer -V'),
            ]);
        });
    }
}
