<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Pagos\StripeProcessor;
use App\Models\Orden;
use App\Models\DetalleCarrito;
use App\Models\Carrito;
use App\Models\User;
use App\Models\Plan;

class StripeController extends Controller
{
    public function procesarPago(Request $r)
    {
        $r->validate([
            'stripe_token' => 'required|string',
            'id_orden' => 'required|integer|exists:orden,id',
            'iduser' => 'required|integer|exists:users,iduser',
            'total' => 'required|numeric|min:1'
        ]);
    
        $orden = Orden::find($r->id_orden);
        $usuario = User::find($r->iduser);
    
        $objeto = new \stdClass();
        $objeto->email = $usuario->email;
        $objeto->token = $r->stripe_token;
        $objeto->precio = $r->total;
        $objeto->currency_code = 'MXN';
    
        // Información adicional
        $objeto->id_item = $r->id_orden;
        $objeto->idusuario = $usuario->iduser;
    
        $stripe = new StripeProcessor();
        $resultado = $stripe->enviar_datos_pago($objeto);
    
        // === Validación del pago como en procesarPagoCarrito ===
        if (isset($resultado->status) && $resultado->status === "OK") {
    
            // Obtener detalles de la orden
            $detalles = \App\Models\DetalleOrden::where('id_orden', $r->id_orden)->get();
    
            foreach ($detalles as $detalle) {
                $detalle->status_pagado = 2; 
                $detalle->metodo_pago = "Stripe";
                $detalle->save();
            }
    
            $resultado->success = true;
            $resultado->message = "Pago de orden exitoso.";
    
        } else {
            $resultado->success = false;
            $resultado->message = "Error al procesar el pago con Stripe.";
        }
    
        return response()->json($resultado);
    }
    

    public function pagarPlan(Request $r)
    {
        $r->validate([
            'stripe_token' => 'required|string',
            'id_plan' => 'required|integer|exists:planes,id_plan',
            'iduser' => 'required|integer|exists:users,iduser',
            'total' => 'required|numeric|min:1'
        ]);

        $plan = Plan::find($r->id_plan);
        $usuario = User::find($r->iduser);

        $objeto = new \stdClass();
        $objeto->email = $usuario->email;
        $objeto->token = $r->stripe_token;
        $objeto->precio = $r->total;
        $objeto->currency_code = 'MXN';
        $objeto->id_item = $plan->id_plan;
        $objeto->idusuario = $usuario->iduser;

        $stripe = new StripeProcessor();
        $resultado = $stripe->enviar_datos_pago($objeto);
        return response()->json($resultado);
    }

    public function procesarPagoCarrito(Request $r)
    {
        $r->validate([
            'stripe_token' => 'required|string',
            'id_carrito' => 'required|integer|exists:carrito,id',
            'iduser' => 'required|integer|exists:users,iduser',
            'total' => 'required|numeric|min:1'
        ]);
    
        $carrito = Carrito::find($r->id_carrito);
        $usuario = User::find($r->iduser);
    
        $objeto = new \stdClass();
        $objeto->email = $usuario->email;
        $objeto->token = $r->stripe_token;
        $objeto->precio = $r->total;
        $objeto->currency_code = 'MXN';
    
        // Información adicional de referencia
        $objeto->id_item = $r->id_carrito;
        $objeto->idusuario = $usuario->iduser;
    
        $stripe = new StripeProcessor();
        $resultado = $stripe->enviar_datos_pago($objeto);
    

        if (isset($resultado->status) && $resultado->status === "OK") {
    

            $detalles = DetalleCarrito::where('id_carrito', $r->id_carrito)->get();
    
            foreach ($detalles as $detalle) {
                $detalle->status_pagado = 2; 
                $detalle->metodo_pago = "Stripe";
                $detalle->save();
            }
    
            $resultado->success = true;
            $resultado->message = "Pago exitoso.";
        } else {

            $resultado->success = false;
            $resultado->message = "Error al procesar el pago con Stripe.";
        }
    
        return response()->json($resultado);
    }
    

}
