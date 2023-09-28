<?php

namespace App\Services\ProjectService;

use App\Helpers\ResponseError;
use App\Models\Shop;
use App\Services\CoreService;
use Illuminate\Support\Facades\Http;

class ProjectService extends CoreService
{
    private $url = 'https://demo.githubit.com/api/v2/server/notification';
    /**
     * @return mixed
     */
    protected function getModelClass()
    {
        return Shop::class;
    }

    public function activationKeyCheck()
    {
        if (!$this->checkLocal()){
            $params = ['code' => config('credential.purchase_code'), 'id' => config('credential.purchase_id'), 'ip' => request()->server('SERVER_ADDR'), 'host' => request()->getSchemeAndHttpHost()];
            $response = Http::post($this->url, $params);
            return $response->body();
        }
        return json_encode([
            'local' => true,
            'active' => true,
            'key' => config('credential.purchase_code'),
        ]);
    }

    /**
     * @throws \Exception
     */
    public function activationError()
    {
        return new \Exception(__('errors.' . ResponseError::ERROR_403), 403);
    }

    public function checkLocal(){
        if ($_SERVER[base64_decode('UkVNT1RFX0FERFI=')] == base64_decode('MTI3LjAuMC4x')
            || $_SERVER[base64_decode('SFRUUF9IT1NU')] == base64_decode('bG9jYWxob3N0')
            || substr($_SERVER[base64_decode('SFRUUF9IT1NU')], 0, 3) == '10.'
            || substr($_SERVER[base64_decode('SFRUUF9IT1NU')], 0, 7) == base64_decode('MTkyLjE2OA==')) {
            return true;
        }
        return false;
    }
}
