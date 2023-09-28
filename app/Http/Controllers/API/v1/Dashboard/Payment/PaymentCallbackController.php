<?php

namespace App\Http\Controllers\API\v1\Dashboard\Payment;

use App\Http\Controllers\Controller;
use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class PaymentCallbackController extends Controller
{
    public function paystackCallbackUrl(Request $request){

        Log::info('Paystack Callback', [$request->all()]);
        $trxId = $request->reference ?? null;

        if ($trxId) {
            $transaction = Transaction::where('payment_trx_id', $trxId)->first();
            $transaction->update([
                'status' => 'paid',
                'status_description' => 'Successfully',
            ]);
        }
        return response()->json('ok');
    }

    public function razorpayCallbackUrl(Request $request) {
        Log::info('Razorpay Callback', [$request->all()]);

        $trxId = $request->razorpay_payment_id ?? null;
        $linkId = $request->razorpay_payment_link_id ?? null;
        $status = $request->razorpay_payment_link_status ?? null;

        if ($status == 'paid') {
            $transaction = Transaction::where('payment_trx_id', $linkId)->first();
            $transaction->update([
                'status' => 'paid',
                'payment_sys_trans_id' => $trxId,
                'status_description' => "Success",
            ]);
        } else {
            $transaction = Transaction::where('payment_trx_id', $linkId)->first();
            $transaction->update([
                'status' => 'rejected',
                'status_description' => $status,
            ]);
        }
    }

    /**
     * Callback method to change transaction by PayPal ID
     */
//    public function paypalCallbackUrl(Request $request)
//    {
//        Log::notice('PAYPAL', [$request->all()]);
//
//        if (isset($request->event_type) && $request->event_type === "CHECKOUT.ORDER.APPROVED") {
//            // Find transaction
//            $transaction = Transaction::firstWhere('payment_trx_id', $request->resource["id"]);
//            if ($transaction) {
//                // Get Shop payment credentials
//                $shopPayment = ShopPayment::with('payment')
//                    ->where(['id_shop' => $transaction->shop_id, 'id_payment' => $transaction->payment_sys_id])->first();
//                // Generate Basic key
//                $basic = base64_encode($shopPayment->key_id . ':' . $shopPayment->secret_id);
//                // Send request to capture order payment
//                $response = $this->paypalRepository->captureOrder($transaction->payment_sys_trans_id, $basic);
//                if ($response && $response->status === "COMPLETED") {
//                    $transaction->update([
//                        'status' => 2,
//                        'status_description' => 'Success',
//                    ]);
//                } else {
//                    $transaction->update([
//                        'status' => 3,
//                        'status_description' => 'ERROR',
//                    ]);
//                }
//            }
//        }
//    }
}
