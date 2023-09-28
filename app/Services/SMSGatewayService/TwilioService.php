<?php

namespace App\Services\SMSGatewayService;

use App\Models\SmsGateway;
use Illuminate\Support\Str;
use Twilio\Rest\Client;

class TwilioService extends \App\Services\CoreService
{
    protected function getModelClass()
    {
        return SmsGateway::class;
    }

    /**
     * @throws \Twilio\Exceptions\ConfigurationException
     */
    public function sendSms($gateway, $phone, $otp): array
    {
        if (!isset($gateway->api_key) || !isset($gateway->secret_key)) {
            return ['status' => false, 'message' => 'Bad credentials. Contact with Support Team'];
        }

        $twilio = new Client($gateway->api_key, $gateway->secret_key);
        $text = Str::replace('#OTP#', $otp['otpCode'], $gateway->text);

        try {
            $response = $twilio->messages
                ->create($phone, // to
                    [
                        "body" => $text,
                        "from" => $gateway->from
                    ]
                );
            if ($response->status == 'queued' || $response->status == 'sent') {
                return ['status' => true];
            } else {
                return ['status' => false, 'message' => $response->message];
            }
        } catch (\Exception $e) {
            return ['status' => false, 'message' => $e->getMessage()];
        }
    }
}
