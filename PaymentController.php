<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use DB;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;
use Razorpay\Api\Api;

class PaymentController extends Controller
{
    public function __construct()
    {

    }
    //++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
    //composer require razorpay/razorpay:2.*
    //++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
    //STYLE-1
    //++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
    public function payment_webhook_style_1(Request $request)
    {
        //++++++++++++++++++++++++++++++++++++++++++++++++
        try {
            $webhookSecret    = 'YOUR_WEBHOOK_SECRET_KEY';
            $webhookSignature = $request->header('X-Razorpay-Signature');
            //++++++++++++++++++++++++++++++++++++++++++++++++
            $api = new Api(env('RAZERPAY_KEY_ID'), env('RAZERPAY_KEY_SECRET'));
            $api->utility->verifyWebhookSignature($request->all(), $webhookSignature, $webhookSecret);
            //++++++++++++++++++++++++++++++++++++++++++++++++
            if (!empty($request['event'])) {
                $payment_ev = $request['event'];
                $payment_ba = $request['payload']['payment']['entity'];
                //++++++++++++++++++++++++++++++++++++++++++++++++
                if ($payment_ev == 'payment.captured') 
                {
                    $payment_id = $payment_ba['id'];
                    $order_id   = $payment_ba['order_id'];
                }
                //++++++++++++++++++++++++++++++++++++++++++++++++
                //++++++++++++++++++++++++++++++++++++++++++++++++
                if ($payment_ev == 'payment.failed') 
                {
                    $payment_id = $payment_ba['id'];
                    $order_id   = $payment_ba['order_id'];
                }
                //++++++++++++++++++++++++++++++++++++++++++++++++
                if ($payment_ev && $payment_ba) {

                    DB::table('table_name')->insert([
                        'order_id'         => $id,
                        'payment_id'       => $payment_id,
                        'payment_order_id' => $order_id,
                        'amount'           => $payment_ba['amount'],
                        'currency'         => $payment_ba['currency'],
                        'email'            => $payment_ba['email'],
                        'contact'          => $payment_ba['contact'],
                        'event'            => $payment_ev,
                        'status'           => $payment_ba['status'],
                        'created_at'       => $request['created_at'],
                    ]);
                }
                //++++++++++++++++++++++++++++++++++++++++++++++++
            }
            //++++++++++++++++++++++++++++++++++++++++++++++++
        } catch (\Exception $e) {
            $errorMessage = $e->getMessage();
        }
        return Response::json(true);
    }
    //++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
    //STYLE-2
    //++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
    public function payment_webhook_style_2(Request $request)
    {
        //++++++++++++++++++++++++++++++++++++++++++++++++
        $secret            = 'YOUR_WEBHOOK_SECRET_KEY';
        $payload           = $request->getContent();
        $signature         = $request->header('X-Razorpay-Signature');
        $expectedSignature = hash_hmac('sha256', $payload, $secret);
        //++++++++++++++++++++++++++++++++++++++++++++++++
        if ($signature == $expectedSignature) 
        {
            $payment_ev = $request['event'];
            $payment_ba = $request['payload']['payment']['entity'];
            //++++++++++++++++++++++++++++++++++++++++++++++++
            if ($payment_ev == 'payment.captured') 
            {
                $payment_id = $payment_ba['id'];
                $order_id   = $payment_ba['order_id'];
            }
            //++++++++++++++++++++++++++++++++++++++++++++++++
            //++++++++++++++++++++++++++++++++++++++++++++++++
            if ($payment_ev == 'payment.failed') 
            {
                $payment_id = $payment_ba['id'];
                $order_id   = $payment_ba['order_id'];
            }
            //++++++++++++++++++++++++++++++++++++++++++++++++
            if ($payment_ev && $payment_ba) {
                DB::table('user_order_payment_transtion')->insert([
                    'order_id'         => $id,
                    'payment_id'       => $payment_id,
                    'payment_order_id' => $order_id,
                    'amount'           => $payment_ba['amount'],
                    'currency'         => $payment_ba['currency'],
                    'email'            => $payment_ba['email'],
                    'contact'          => $payment_ba['contact'],
                    'event'            => $payment_ev,
                    'status'           => $payment_ba['status'],
                    'created_at'       => $request['created_at'],
                ]);
            }
            //++++++++++++++++++++++++++++++++++++++++++++++++
        } else {
            $errorMessage = 'Signature Mismatch';
        }
        //++++++++++++++++++++++++++++++++++++++++++++++++
        return Response::json(true);
    }
//END
}
