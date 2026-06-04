<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use MercadoPago\MercadoPagoConfig;
use MercadoPago\Client\Preference\PreferenceClient;
use MercadoPago\Client\Payment\PaymentClient;
use App\Models\Orden;

class MercadoPagoController extends Controller
{
    // 🔹 Crear preferencia y registrar pago pendiente
    public function crearPreferencia(Request $request)
    {
        MercadoPagoConfig::setAccessToken(env('MERCADOPAGO_ACCESS_TOKEN'));

        $client = new PreferenceClient();

        $preference = $client->create([
            "items" => [
                [
                    "title" => "Orden #" . $request->id_orden,
                    "quantity" => 1,
                    "unit_price" => floatval($request->total),
                    "currency_id" => "MXN"
                ]
            ],
            "auto_return" => "approved",
        ]);

        // Guardar el método y marcar como pendiente
        $orden = Orden::find($request->id_orden);
        if ($orden) {
            $orden->metodo_pago = "MercadoPago";
            $orden->status_pago = "pendiente";
            $orden->save();
        }

        return response()->json([
            "init_point" => $preference->init_point
        ]);
    }

    // 🔹 Webhook para actualizar pago
    public function webhook(Request $request)
    {
        try {
            $data = $request->all();
            if (!isset($data['data']['id'])) {
                return response()->json(['message' => 'No hay ID de pago'], 400);
            }

            $paymentId = $data['data']['id'];
            MercadoPagoConfig::setAccessToken(env('MERCADOPAGO_ACCESS_TOKEN'));

            $paymentClient = new PaymentClient();
            $payment = $paymentClient->get($paymentId);

            // Puedes guardar log si quieres ver qué devuelve Mercado Pago
            // \Log::info('Webhook MercadoPago:', ['payment' => $payment]);

            if (isset($payment->status) && $payment->status === 'approved') {
                // Extraer ID de orden del título
                $title = $payment->description ?? $payment->additional_info->items[0]->title ?? '';
                preg_match('/#(\d+)/', $title, $matches);
                $idOrden = $matches[1] ?? null;

                if ($idOrden) {
                    $orden = Orden::find($idOrden);
                    if ($orden) {
                        $orden->status_pago = 'pagado';
                        $orden->save();
                    }
                }
            }

            return response()->json(['message' => 'OK']);
        } catch (\Exception $e) {
            \Log::error('Error Webhook MercadoPago:', ['error' => $e->getMessage()]);
            return response()->json(['error' => 'Error interno'], 500);
        }
    }
}
