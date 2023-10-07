<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class PaymentController extends Controller
{
    public function payment(Request $request)
    {

        $baseURL = 'https://sandbox.uddoktapay.com/';
        $apiKEY = config('uddoktapay.api_key');

        $fields = [
            'full_name' => 'Nazmul',
            'email'     => 'nazmul.ns7989@gmail.com',
            'amount'    => '100',
            'metadata'  => [
                'user_id'  => '10',
                'order_id' => '50'
            ],
            'redirect_url' => route('success'),
            'cancel_url'   => route('cancel'),
            'webhook_url'  => ''
        ];


        $curl = curl_init();

        curl_setopt_array($curl, [
            CURLOPT_URL => $baseURL . "api/checkout-v2",
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "POST",
            CURLOPT_POSTFIELDS => json_encode($fields),
            CURLOPT_HTTPHEADER => [
                "RT-UDDOKTAPAY-API-KEY: " . $apiKEY,
                "accept: application/json",
                "content-type: application/json"
            ],
        ]);

        $response = curl_exec($curl);

        curl_close($curl);

       $responseObject = json_decode($response, true);

       if ($responseObject['status'] == true && $responseObject['payment_url'] != null) {
            return redirect()->away($responseObject['payment_url']);
       }else{
           return redirect()->route('home')->with('error', 'Payment Url Generation Failed!');
       }


    }

    public function success(Request $request)
    {

        if (isset($request['invoice_id']) && $request['invoice_id'] != null) {

            $baseURL = 'https://sandbox.uddoktapay.com/';
            $apiKEY = config('uddoktapay.api_key');

            $fields = [
                'invoice_id'     => $request['invoice_id']
            ];

            $curl = curl_init();

            curl_setopt_array($curl, [
                CURLOPT_URL => $baseURL . "api/verify-payment",
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_ENCODING => "",
                CURLOPT_MAXREDIRS => 10,
                CURLOPT_TIMEOUT => 30,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST => "POST",
                CURLOPT_POSTFIELDS => json_encode($fields),
                CURLOPT_HTTPHEADER => [
                    "RT-UDDOKTAPAY-API-KEY: " . $apiKEY,
                    "accept: application/json",
                    "content-type: application/json"
                ],
            ]);

            $response = curl_exec($curl);

            curl_close($curl);

           $responseObject = json_decode($response, true);

//           dd($responseObject);

            return redirect()->route('home')->with('success', 'Order placed successfully.');

        }else{
            return redirect()->route('home')->with('error', 'Payment Failed!');
        }
    }

    public function cancel()
    {
        return redirect()->route('home')->with('error', 'Payment Url Generation Failed!');
    }



}
