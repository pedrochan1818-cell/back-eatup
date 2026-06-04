<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Orden;
use App\Models\Carrito;
use Carbon\Carbon; 

class CocinaController extends Controller
{
    // Función para obtener solo las reservas activas
    public function obtenerReservas()
    {
        $reservas = DB::table('reservas as r')
            ->leftJoin('orden as o', 'r.id_reserva', '=', 'o.id_reserva') // une reservas con orden
            ->leftJoin('detalle_orden as d', 'o.id', '=', 'd.id_orden')   // une con detalle
            ->select(
                'r.id_reserva',
                'r.nombre',
                'r.no_persona',
                'r.fecha',
                'r.hora',
                'r.fecha_reserva',
                'r.id_mesa',
                'r.id_user',
                'r.clave',
                'r.qr_image',
                'r.status as status_reserva',
                'r.orden as orden', // 👈 este campo
                'o.id as id_orden',
                'o.status as estado_orden',
                'o.hora_comida',
                'd.idproducto',
                'd.cantidad',
                'd.precio_unitario',
                'd.subtotal'
            )
            
            ->get();
    
        // Agrupar por reserva
        $reservasAgrupadas = $reservas->groupBy('id_reserva')->map(function ($items) {
            $reserva = $items->first();
            return [
                'id_reserva' => $reserva->id_reserva,
                'nombre' => $reserva->nombre,
                'no_persona' => $reserva->no_persona,
                'fecha' => $reserva->fecha,
                'hora' => $reserva->hora,
                'fecha_reserva' => $reserva->fecha_reserva,
                'id_mesa' => $reserva->id_mesa,
                'id_user' => $reserva->id_user,
                'clave' => $reserva->clave,
                'qr_image' => $reserva->qr_image,
                'status_reserva' => $reserva->status_reserva,
                'orden' => $reserva->id_orden ? [
                    'id_orden' => $reserva->id_orden,
                    'estado_orden' => $reserva->estado_orden,
                    'hora_comida' => $reserva->hora_comida,
                    'productos' => $items->map(function ($item) {
                        return [
                            'idproducto' => $item->idproducto,
                            'cantidad' => $item->cantidad,
                            'precio_unitario' => $item->precio_unitario,
                            'subtotal' => $item->subtotal,
                        ];
                    })->filter()->values()
                ] : null
            ];
        })->values();
    
        return response()->json($reservasAgrupadas);
    }
    

    public function obtenerOrdenes()
    {
        $ordenes = DB::table('orden as o')
            ->join('detalle_orden as d', 'o.id', '=', 'd.id_orden')
            ->leftJoin('reservas as r', 'o.id_reserva', '=', 'r.id_reserva') 
            ->select(
                'o.id as id_orden',
                'o.id_user',
                'o.id_reserva',
                'o.fecha as fecha_orden',
                'o.status as status_comida',
                'o.hora_comida',
                'd.idproducto',
                'd.cantidad',
                'd.precio_unitario',
                'd.subtotal',
                'd.status_pagado',   
                'd.metodo_pago',
                'r.nombre as cliente_reserva',
                'r.no_persona',
                'r.fecha as fecha_reserva',
                'r.hora as hora_reserva',
                'r.id_mesa'
            )
            ->whereIn('d.status_pagado', [2, 3])
            ->get();
    
        // Agrupar por orden
        $ordenesAgrupadas = $ordenes->groupBy('id_orden')->map(function ($items) {
            $orden = $items->first();
            return [
                'id_orden' => $orden->id_orden,
                'id_user' => $orden->id_user,
                'id_reserva' => $orden->id_reserva,
                'fecha_orden' => $orden->fecha_orden,
                'status_comida' => $orden->status_comida,
                'hora_comida' => $orden->hora_comida,
                'productos' => $items->map(function ($item) {
                    return [
                        'idproducto' => $item->idproducto,
                        'cantidad' => $item->cantidad,
                        'precio_unitario' => $item->precio_unitario,
                        'subtotal' => $item->subtotal,
                        'status_pagado' => $item->status_pagado,
                        'metodo_pago' => $item->metodo_pago,
                    ];
                })->values(),
                'reserva' => $orden->id_reserva ? [
                    'cliente_reserva' => $orden->cliente_reserva,
                    'no_persona' => $orden->no_persona,
                    'fecha_reserva' => $orden->fecha_reserva,
                    'hora_reserva' => $orden->hora_reserva,
                    'id_mesa' => $orden->id_mesa,
                ] : null
            ];
        })->values();
    
        return response()->json($ordenesAgrupadas);
    }
    public function cambiarEstado(Request $request, $id_orden)
    {
        $request->validate([
            'estado_orden' => 'required|integer'
        ]);

        // Buscar la orden
        $orden = Orden::findOrFail($id_orden);

        // Actualizar estado
        $orden->status = $request->estado_orden;
        $orden->save();

        return response()->json([
            'message' => 'Estado de la orden actualizado correctamente',
            'orden' => $orden
        ], 200);
    }

    
public function obtenerHoraServidor()
{
    $horaActual = Carbon::now('America/Mexico_City'); // Puedes ajustar la zona si deseas
    return response()->json([
        'hora_actual' => $horaActual->toDateTimeString(),
    ]);
}
public function obtenerCarritos()
{
    $carritos = DB::table('carrito as c')
        ->join('detalle_carrito as d', 'c.id', '=', 'd.id_carrito')
        ->leftJoin('users as u', 'c.id_user', '=', 'u.iduser')
        ->select(
            'c.id as id_carrito',
            'c.id_user',
            'c.fecha as fecha_carrito',
            'c.status as status_carrito',
            'c.hora_comida',
            'c.id_turno',
            'd.idproducto',
            'd.cantidad',
            'd.precio_unitario',
            'd.subtotal',
            'd.status_pagado',
            'd.metodo_pago',
            'u.nombre as nombre_usuario',
            'u.email'
        )
        ->whereIn('d.status_pagado', [2])
        ->get();

    // Agrupar por carrito
    $carritosAgrupados = $carritos->groupBy('id_carrito')->map(function ($items) {
        $carrito = $items->first();
        return [
            'id_carrito' => $carrito->id_carrito,
            'id_user' => $carrito->id_user,
            'fecha_carrito' => $carrito->fecha_carrito,
            'status_carrito' => $carrito->status_carrito,
            'hora_comida' => $carrito->hora_comida,
            'id_turno' => $carrito->id_turno,
            'usuario' => [
                'nombre' => $carrito->nombre_usuario,
                'email' => $carrito->email
            ],
            'productos' => $items->map(function ($item) {
                return [
                    'idproducto' => $item->idproducto,
                    'cantidad' => $item->cantidad,
                    'precio_unitario' => $item->precio_unitario,
                    'subtotal' => $item->subtotal,
                    'status_pagado' => $item->status_pagado,
                    'metodo_pago' => $item->metodo_pago
                ];
            })->values()
        ];
    })->values();

    return response()->json($carritosAgrupados);
}

    
public function cambiarEstadoCarrito(Request $request, $id_carrito)
{
    $request->validate([
        'estado_carrito' => 'required|integer'
    ]);

    // Buscar la orden
    $carrito = Carrito::findOrFail($id_carrito);

    // Actualizar estado
    $carrito->status = $request->estado_carrito;
    $carrito->save();

    return response()->json([
        'message' => 'Estado de la carrito actualizado correctamente',
        'carrito' => $carrito
    ], 200);
}


public function obtenerCarritosPorUsuario(Request $request)
{
    $userId = $request->query('user'); // ID del usuario
    if (!$userId) {
        return response()->json([
            'success' => false,
            'message' => 'Usuario no especificado'
        ], 400);
    }

    $carritos = DB::table('carrito as c')
        ->join('detalle_carrito as d', 'c.id', '=', 'd.id_carrito')
        ->leftJoin('users as u', 'c.id_user', '=', 'u.iduser')
        ->leftJoin('producto as p', 'd.idproducto', '=', 'p.idproducto')
        ->where('c.id_user', $userId)
        ->select(
            'c.id as id_carrito',
            'c.id_user',
            'c.fecha as fecha_carrito',
            'c.status as status_carrito',
            'c.hora_comida',
            'c.id_turno',
            'd.idproducto',
            'd.cantidad',
            'd.precio_unitario',
            'd.subtotal',
            'd.status_pagado',
            'd.metodo_pago',
            'p.nombre as nombre_producto',
            'u.nombre as nombre_usuario',
            'u.email'
        )
        ->get();

    $carritosAgrupados = $carritos->groupBy('id_carrito')->map(function ($items) {
        $carrito = $items->first();
        return [
            'id_carrito' => $carrito->id_carrito,
            'id_user' => $carrito->id_user,
            'fecha_carrito' => $carrito->fecha_carrito,
            'status_carrito' => $carrito->status_carrito,
            'hora_comida' => $carrito->hora_comida,
            'id_turno' => $carrito->id_turno,
            'usuario' => [
                'nombre' => $carrito->nombre_usuario,
                'email' => $carrito->email
            ],
            'productos' => $items->map(function ($item) {
                return [
                    'idproducto' => $item->idproducto,
                    'nombre' => $item->nombre_producto,
                    'cantidad' => $item->cantidad,
                    'precio_unitario' => $item->precio_unitario,
                    'subtotal' => $item->subtotal,
                    'status_pagado' => $item->status_pagado,
                    'metodo_pago' => $item->metodo_pago
                ];
            })->values()
        ];
    })->values();

    return response()->json([
        'success' => true,
        'carritos' => $carritosAgrupados
    ]);
}


}
