See Documentation here - https://uddoktapay.readme.io/reference/api-information

### # CSRF Token Error / 419
In Laravel, you can handle CSRF Token Errors (status code 419) by customizing the "VerifyCsrfToken middleware", which is located in the "app/Http/Middleware/VerifyCsrfToken.php" file. To address this error, you need to declare the success and cancel URLs/routes in the middleware.


```
<?php

namespace App\Http\Middleware;

use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken as Middleware;

class VerifyCsrfToken extends Middleware
{
    /**
     * The URIs that should be excluded from CSRF verification.
     *
     * @var array<int, string>
     */
    protected $except = [
        'success',
        'cancel'
    ];
}
```

### Handle Session Log Out Error in Laravel
The error is related to the 'same_site' and 'secure' options in the config/session.php configuration file.

<pre>
Change 'secure' => env('SESSION_SECURE_COOKIE'), to 'secure' => true;<br>
Change 'same_site' => 'lax', to 'same_site' => 'none';
</pre>

### Update .env file
``` 
UDDOKTAPAY_API_KEY= Your api key goes here
```

### Create a file in config/uddoktapay.php and update it by the following code
``` 
<?php

return [
    'api_key' => env('UDDOKTAPAY_API_KEY')
];
```

### Update routes/web.php file

``` 
Route::get('/payment',[PaymentController::class,'payment'])->name('payment');

Route::post('/success', [PaymentController::class,'success'])->name('success');
Route::get('/cancel', [PaymentController::class,'cancel'])->name('cancel');


```

### Create PaymentController.php file and update it by the following code

``` 
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
            'email'     => 'example@gmail.com',
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
            //Store payment information and order details in your database

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


```
