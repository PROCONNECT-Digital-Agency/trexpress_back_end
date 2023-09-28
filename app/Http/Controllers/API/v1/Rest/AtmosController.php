<?php

namespace App\Http\Controllers\API\v1\Rest;

use App\Models\Order;
use App\Models\Card;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class AtmosController extends RestBaseController
{
    /**
     * Get Atmos token.
     *
     * @return \Illuminate\Http\JsonResponse|\Illuminate\Http\Resources\Json\AnonymousResourceCollection|\Illuminate\Http\Response
     */
    public function token()
    {
        $result = Http::asForm()
            ->withHeaders([
                'Authorization' => 'Basic Wkx4WXRJRkh4WW5RWHBHc3RIN01tNkZ5NzlJYTptVkRTZldKSUYwTTRBejlyWXRjWTlLZlRuc0Fh'
            ])->post(
                'https://partner.paymo.uz/token',
                ['grant_type' => 'client_credentials']
            );

        return ['token' => $result['access_token']];
    }

    public function confirm(Request $request) {
        $order = Order::findOrFail($request->invoice);

        if ($order->transaction->price * 100 === (float) $request->amount) {
            $order->transaction->status = 'paid';
            $order->transaction->update();

            return [
                "status" => 1,
                "message" => "Успешно"
            ];
        } else {
            $order->transaction->status = 'rejected';
            $order->transaction->update();
        }
    }

    public function card(Request $request)
    {
        $card = new Card;
        $card['card_id'] = $request->card_id;
        $card['card_number'] = $request->card_number;
        $card['pan'] = $request->pan;
        $card['expiry'] = $card->expiry;
        $card['card_token'] = $card->card_token;
        $card['card_holder'] = $card->card_holder;
        $card['user_id'] = $card->user_id;

        $card->save();
        return [
                "status" => 1,
                "message" => "Успешно"
            ];
    }
}
