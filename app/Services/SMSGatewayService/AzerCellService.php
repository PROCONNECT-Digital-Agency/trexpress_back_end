<?php

namespace App\Services\SMSGatewayService;

use App\Models\SmsGateway;
use Illuminate\Support\Facades\Http;
use Twilio\Exceptions\ConfigurationException;

class AzerCellService extends \App\Services\CoreService
{
    protected string $baseUrl = 'http://www.poctgoyercini.com/api_http/sendsms.asp';

    protected function getModelClass()
    {
        return SmsGateway::class;
    }

    /**
     * @throws ConfigurationException
     */
    public function sendSms($phone, $otp)
    {
        try {
            $response = Http::get($this->baseUrl.'?'.'user='.config('azerCell.username').'&'.'password='.config('azerCell.password').'&'.'gsm='.$phone.'&'.'text=Confirmation code for mupza.com '.$otp['otpCode']);
            if ($response->status() == 200) {
                return ['status' => true];
            } else {
                return ['status' => false, 'message' => $response->message];
            }
        } catch (\Exception $e) {
            return ['status' => false, 'message' => $e->getMessage()];
        }
    }

}
