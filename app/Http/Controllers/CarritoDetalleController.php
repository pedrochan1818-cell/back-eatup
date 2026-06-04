<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\DetalleCarrito;
use App\Models\Carrito;
class CarritoDetalleController extends Controller
{
    // ======================================================
    // Obtener detalles del carrito de un usuario
    // ======================================================
    public function obtenerDetalles($iduser)
    {
        // 1. Obtener el carrito más reciente del usuario
        $carrito = Carrito::where('id_user', $iduser)
            ->orderBy('id', 'desc') // esto sí existe
            ->first();
    
        if (!$carrito) {
            return response()->json([]);
        }
    
        // 2. Obtener SOLO los productos del carrito más reciente
        $detalles = DetalleCarrito::with('producto')
            ->where('id_carrito', $carrito->id)
            ->get();
    
        return response()->json($detalles);
    }
    


    // ======================================================
    // Operaciones CRUD
    // ======================================================
    public function operarDetalle(Request $request)
    {
        $operacion = $request->operacion ?? null;

        switch ($operacion) {


            /* ==========================================
                AGREGAR (numero_pedido = 1)
            ========================================== */
            case 'Agregar':
                $request->validate([
                    'iduser' => 'required|integer',
                    'idproducto' => 'required|integer',
                    'cantidad' => 'required|integer',
                    'precio_unitario' => 'required|numeric',
                    'id_carrito' => 'required|integer'
                ]);

                $subtotal = $request->cantidad * $request->precio_unitario;

                $detalle = DetalleCarrito::where('iduser', $request->iduser)
                    ->where('idproducto', $request->idproducto)
                    ->where('id_carrito', $request->id_carrito)
                    ->where('numero_pedido', 1)
                    ->first();

                if ($detalle) {
                    $detalle->cantidad += $request->cantidad;
                    $detalle->subtotal = $detalle->cantidad * $detalle->precio_unitario;
                } else {
                    $detalle = new DetalleCarrito([
                        'iduser' => $request->iduser,
                        'idproducto' => $request->idproducto,
                        'cantidad' => $request->cantidad,
                        'precio_unitario' => $request->precio_unitario,
                        'subtotal' => $subtotal,
                        'id_carrito' => $request->id_carrito,
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



            /* ==========================================
                AGREGAR REPEDIDO (numero_pedido = 2)
            ========================================== */
            case 'AgregarRepedido':
                $request->validate([
                    'iduser' => 'required|integer',
                    'idproducto' => 'required|integer',
                    'cantidad' => 'required|integer',
                    'precio_unitario' => 'required|numeric',
                    'id_carrito' => 'required|integer'
                ]);

                $subtotal = $request->cantidad * $request->precio_unitario;

                $detalle = DetalleCarrito::where('iduser', $request->iduser)
                    ->where('idproducto', $request->idproducto)
                    ->where('id_carrito', $request->id_carrito)
                    ->where('numero_pedido', 2)
                    ->first();

                if ($detalle) {
                    $detalle->cantidad += $request->cantidad;
                    $detalle->subtotal = $detalle->cantidad * $detalle->precio_unitario;
                } else {
                    $detalle = new DetalleCarrito([
                        'iduser' => $request->iduser,
                        'idproducto' => $request->idproducto,
                        'cantidad' => $request->cantidad,
                        'precio_unitario' => $request->precio_unitario,
                        'subtotal' => $subtotal,
                        'id_carrito' => $request->id_carrito,
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



            /* ==========================================
                ELIMINAR
            ========================================== */
            case 'Eliminar':
                $request->validate([
                    'iduser' => 'required|integer',
                    'idproducto' => 'required|integer',
                    'id_carrito' => 'required|integer'
                ]);

                $detalle = DetalleCarrito::where('iduser', $request->iduser)
                    ->where('idproducto', $request->idproducto)
                    ->where('id_carrito', $request->id_carrito)
                    ->first();

                if (!$detalle) {
                    return response()->json(['error' => 'Detalle no encontrado'], 404);
                }

                $detalle->delete();

                return response()->json([
                    'message' => 'Detalle eliminado correctamente'
                ], 200);



            /* ==========================================
                ACTUALIZAR
            ========================================== */
            case 'Actualizar':
                $request->validate([
                    'iduser' => 'required|integer',
                    'idproducto' => 'required|integer',
                    'cantidad' => 'required|integer',
                    'id_carrito' => 'required|integer'
                ]);

                $detalle = DetalleCarrito::where('iduser', $request->iduser)
                    ->where('idproducto', $request->idproducto)
                    ->where('id_carrito', $request->id_carrito)
                    ->first();

                if ($detalle) {
                    $detalle->cantidad = $request->cantidad;
                    $detalle->subtotal = $detalle->cantidad * $detalle->precio_unitario;
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


    // ======================================================
    // Actualizar subtotal manualmente
    // ======================================================
    public function actualizarSubtotal($id)
    {
        $detalle = DetalleCarrito::find($id);

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
    public function marcarPagado(Request $request, $id_carrito)
{
    $request->validate([
        'metodo_pago' => 'required|string|max:50'
    ]);

    try {
        // Obtener todos los detalles del carrito
        $detalles = DetalleCarrito::where('id_carrito', $id_carrito)->get();

        foreach ($detalles as $detalle) {
            $detalle->status_pagado = 2; // Pagado
            $detalle->metodo_pago = $request->metodo_pago;
            $detalle->save();
        }

        return response()->json([
            'success' => true,
            'message' => 'Pago registrado correctamente en todos los detalles del carrito',
            'detalles' => $detalles
        ]);

    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'message' => 'Error al actualizar el pago',
            'error' => $e->getMessage()
        ], 500);
    }
}


}
