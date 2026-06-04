<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\DetalleOrden;
use App\Models\Orden;

class DetalleController extends Controller
{
    public function obtenerDetalles($iduser)
    {
        // 1. Obtener la orden MÁS RECIENTE del usuario
        $orden = Orden::where('id_user', $iduser)
            ->orderBy('id', 'desc') // Orden más reciente
            ->first();
    
        if (!$orden) {
            return response()->json([]);
        }
    
        // 2. Obtener SOLO los productos de esa orden
        $detalles = DetalleOrden::with('producto')
            ->where('id_orden', $orden->id)
            ->get();
    
        return response()->json($detalles);
    }
    

    // Manejar operaciones con switch
    public function operarDetalle(Request $request)
    {
        $operacion = $request->operacion ?? null;

        switch ($operacion) {

            /* ======================================================
               ==========    AGREGAR con numero_pedido = 1   =========
               ====================================================== */
            case 'Agregar':
                $request->validate([
                    'iduser' => 'required|integer',
                    'idproducto' => 'required|integer'
                ]);

                $subtotal = $request->cantidad * $request->precio_unitario;

                // Verificar si ya existe el detalle
                $detalle = DetalleOrden::where('iduser', $request->iduser)
                              ->where('idproducto', $request->idproducto)
                              ->where('id_orden', $request->id_orden)
                              ->where('numero_pedido', 1)
                              ->first();

                if ($detalle) {
                    $detalle->cantidad += $request->cantidad;
                    $detalle->subtotal = $detalle->cantidad * $detalle->precio_unitario;
                } else {
                    // Crear nuevo detalle siempre con numero_pedido = 1
                    $detalle = new DetalleOrden([
                        'iduser' => $request->iduser,
                        'idproducto' => $request->idproducto,
                        'cantidad' => $request->cantidad,
                        'precio_unitario' => $request->precio_unitario,
                        'subtotal' => $subtotal,
                        'id_orden' => $request->id_orden,
                        'numero_pedido' => 1,
                        'status_pagado' => 1,   
                        'metodo_pago' => 'Pendiente'
                    ]);
                }

                $detalle->save();

                return response()->json([
                    'message' => 'Detalle agregado correctamente',
                    'detalle' => $detalle
                ], 201);


            /* ======================================================
               ==========  AGREGAR REPEDIDO (numero_pedido = 2)  =====
               ====================================================== */
            case 'AgregarRepedido':
                $request->validate([
                    'iduser' => 'required|integer',
                    'idproducto' => 'required|integer'
                ]);

                $subtotal = $request->cantidad * $request->precio_unitario;

                // Verificar si ya existe detalle de REPEDIDO (2)
                $detalle = DetalleOrden::where('iduser', $request->iduser)
                              ->where('idproducto', $request->idproducto)
                              ->where('id_orden', $request->id_orden)
                              ->where('numero_pedido', 2)
                              ->first();

                if ($detalle) {
                    $detalle->cantidad += $request->cantidad;
                    $detalle->subtotal = $detalle->cantidad * $detalle->precio_unitario;
                } else {
                    // Crear nuevo detalle con numero_pedido = 2
                    $detalle = new DetalleOrden([
                        'iduser' => $request->iduser,
                        'idproducto' => $request->idproducto,
                        'cantidad' => $request->cantidad,
                        'precio_unitario' => $request->precio_unitario,
                        'subtotal' => $subtotal,
                        'id_orden' => $request->id_orden,
                        'numero_pedido' => 2,
                        'status_pagado' => 1,     
                        'metodo_pago' => 'Pendiente'
                    ]);
                }

                $detalle->save();

                return response()->json([
                    'message' => 'Detalle REPEDIDO agregado correctamente',
                    'detalle' => $detalle
                ], 201);


            /* ======================================================
               ======================   ELIMINAR   ===================
               ====================================================== */
            case 'Eliminar':
                $request->validate([
                    'iduser' => 'required|integer',
                    'idproducto' => 'required|integer'
                ]);

                $detalle = DetalleOrden::where('iduser', $request->iduser)
                              ->where('idproducto', $request->idproducto)
                              ->where('id_orden', $request->id_orden)
                              ->first();

                if (!$detalle) {
                    return response()->json(['error' => 'Detalle no encontrado'], 404);
                }

                $detalle->delete();

                return response()->json([
                    'message' => 'Detalle eliminado correctamente'
                ], 200);


            /* ======================================================
               =====================   ACTUALIZAR   ==================
               ====================================================== */
            case 'Actualizar':
                $request->validate([
                    'iduser' => 'required|integer',
                    'idproducto' => 'required|integer'
                ]);

                $detalle = DetalleOrden::where('iduser', $request->iduser)
                              ->where('idproducto', $request->idproducto)
                              ->where('id_orden', $request->id_orden)
                              ->first();

                if ($detalle) {
                    $detalle->cantidad = $request->cantidad;
                    $detalle->subtotal = $request->cantidad * $detalle->precio_unitario;
                    $detalle->save();
                } else {
                    return response()->json(['error' => 'Detalle no encontrado'], 404);
                }

                return response()->json([
                    'message' => 'Detalle actualizado correctamente',
                    'detalle' => $detalle
                ], 200);


            default:
                return response()->json(['error' => 'Operación no válida'], 400);
        }
    }

    // Obtener detalles por reserva
    public function obtenerDetallesPorReserva($id_reserva)
    {
        return response()->json(
            DetalleOrden::with('producto')
                  ->where('id_reserva', $id_reserva)
                  ->get()
        );
    }

    // Actualizar subtotal automáticamente
    public function actualizarSubtotal($id)
    {
        $detalle = DetalleOrden::find($id);
        
        if (!$detalle) {
            return response()->json(['error' => 'Detalle no encontrado'], 404);
        }

        $detalle->subtotal = $detalle->cantidad * $detalle->precio_unitario;
        $detalle->save();

        return response()->json([
            'message' => 'Subtotal actualizado correctamente',
            'detalle' => $detalle
        ], 200);
    }
}
