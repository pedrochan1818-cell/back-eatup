<?php

namespace App\Pagos;

require_once base_path('vendor/autoload.php');
use Stripe\Stripe;
use Stripe\Customer;
use Stripe\Charge;
use Stripe\HttpClient\CurlClient;
use Stripe\ApiRequestor;

class StripeProcessor
{
    var $objeto_stripe;

    function __construct()
    {
        $curl = new CurlClient([CURLOPT_SSLVERSION => CURL_SSLVERSION_TLSv1_2]);
        $this->objeto_stripe = new Stripe();
        ApiRequestor::setHttpClient($curl);
        $this->objeto_stripe->setVerifySslCerts(false);
        $this->objeto_stripe->setApiKey(env('STRIPE_SECRET'));
    }

    function crear_customer($objeto)
    {
        return Customer::create([
            'email' => $objeto->email,
            'source' => $objeto->token,
        ]);
    }

    function enviar_datos_pago($objeto)
    {
        $customer = $this->crear_customer($objeto);

        $charge = Charge::create([
            'customer' => $customer->id,
            'amount' => $objeto->precio * 100,
            'currency' => $objeto->currency_code,
            'metadata' => ['order_id' => $objeto->id_item]
        ]);

        $result = $charge->jsonSerialize();
        $res = new \stdClass();

        if ($result['status'] === 'succeeded') {
            $res->status = 'OK';
            $res->transaccion = $result;
        } else {
            $res->status = 'Error';
            $res->transaccion = null;
        }

        return $res;
    }
}
