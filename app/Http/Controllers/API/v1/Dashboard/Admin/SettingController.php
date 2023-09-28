<?php

namespace App\Http\Controllers\API\v1\Dashboard\Admin;

use App\Models\Settings;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class SettingController extends AdminBaseController
{
    private Settings $model;

    public function __construct(Settings $model)
    {
        parent::__construct();
        $this->model = $model;
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(): \Illuminate\Http\JsonResponse
    {
        $settings = $this->model->adminSettings();

        return $this->successResponse(trans('web.list_of_settings', [], \request()->lang), $settings);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     * @throws \Psr\SimpleCache\InvalidArgumentException
     */
    public function store(Request $request)
    {
        foreach ($request->all() as $index => $item) {
            $this->model->updateOrCreate(['key' => $index],[
                'value' => $item
            ]);
        }
        cache()->delete('admin-settings');
       return $this->successResponse(trans('web.record_has_been_successfully_created', [], \request()->lang));

    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
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

    public function clearCache()
    {
        Artisan::call('cache:clear');
        return $this->successResponse( __('web.cache_successfully_clear'), []);
    }
}
