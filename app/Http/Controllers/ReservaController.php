<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Reserva;
use App\Models\Mesa;
use App\Models\Orden;
use App\Models\DetalleOrden;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class ReservaController extends Controller
{
    public function store(Request $request)
{
    $validator = Validator::make($request->all(), [
        'nombre' => 'required|string|max:150',
        'no_persona' => 'required|integer|min:1',
        'fecha' => 'required|date',
        'hora' => 'required|date_format:H:i',
        'id_mesa' => 'required|integer',
        'id_user' => 'required|integer',
        'id_turno' => 'required|integer|exists:turno,id_turno', // 👈 NUEVO CAMPO
        'qr_image' => 'nullable|string'
    ]);

    if ($validator->fails()) {
        return response()->json([
            'success' => false,
            'errors' => $validator->errors()
        ], 422);
    }

    DB::beginTransaction();

    try {
        $reserva = Reserva::create([
            'nombre' => $request->nombre,
            'no_persona' => $request->no_persona,
            'fecha' => $request->fecha,
            'hora' => $request->hora,
            'fecha_reserva' => now(),
            'id_mesa' => $request->id_mesa,
            'id_user' => $request->id_user,
            'id_turno' => $request->id_turno, // 👈 GUARDAR AQUÍ
            'clave' => Str::random(10),
            'orden' => 1,
            'qr_image' => 'default.png',
            'status' => 1
        ]);

        if ($request->has('qr_image') && !empty($request->qr_image)) {
            $this->guardarQR($reserva, $request->qr_image);
        }

        $mesa = Mesa::find($request->id_mesa);
        if ($mesa) {
            $mesa->estado = 'ocupada';
            $mesa->save();
        }

        DB::commit();

        return response()->json([
            'success' => true,
            'reserva' => [
                'id_reserva' => $reserva->id_reserva,
                'clave' => $reserva->clave,
                'qr_image' => $reserva->qr_image,
                'status' => $reserva->status,
                'id_turno' => $reserva->id_turno, // 👈 OPCIONAL EN RESPUESTA
                'es_ultima' => true
            ],
            'message' => 'Reserva creada exitosamente'
        ]);

    } catch (\Exception $e) {
        DB::rollBack();
        return response()->json([
            'success' => false,
            'message' => 'Error al crear la reserva',
            'error' => $e->getMessage()
        ], 500);
    }
}

    // ======== NUEVOS MÉTODOS PARA MANEJO DE STATUS ========
    public function actualizarStatus(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id_reserva' => 'required|integer|exists:reservas,id_reserva',
            'status' => 'required|integer|in:1,2,3' // 1=Activa, 2=Completada, 3=Cancelada
        ]);
    
        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }
    
        DB::beginTransaction();
    
        try {
            $reserva = Reserva::find($request->id_reserva);
            $reserva->status = $request->status;
            
            if ($request->status == 2) { 
                $reserva->orden = 2;
                Mesa::where('id', $reserva->id_mesa)
                    ->update(['estado' => 'disponible']);
                
                // Actualizar el status de la orden asociada a 2
                Orden::where('id_reserva', $request->id_reserva)
                    ->update(['status' => 2]);
            }
            
            $reserva->save();
    
            DB::commit();
    
            return response()->json([
                'success' => true,
                'reserva' => $reserva,
                'message' => 'Estado actualizado correctamente'
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Error al actualizar estado',
                'error' => $e->getMessage()
            ], 500);
        }
    }
    public function porStatus($status)
    {
        try {
            $reservas = Reserva::where('status', $status)
                             ->orderBy('fecha', 'asc')
                             ->get();

            return response()->json([
                'success' => true,
                'reservas' => $reservas
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener reservas',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    // ======== MÉTODOS ORIGINALES (SIN MODIFICACIONES) ========
    public function saveQR(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'reserva_id' => 'required|integer|exists:reservas,id_reserva',
            'qr_image' => 'required|string'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $reserva = Reserva::findOrFail($request->reserva_id);
            
            if ($reserva->qr_image && Storage::disk('public')->exists('qrcodes/'.$reserva->qr_image)) {
                Storage::disk('public')->delete('qrcodes/'.$reserva->qr_image);
            }

            $fileName = 'reserva_'.$reserva->id_reserva.'_'.time().'.png';
            $imageData = base64_decode($request->qr_image);
            Storage::disk('public')->put('qrcodes/'.$fileName, $imageData);

            $reserva->qr_image = $fileName;
            $reserva->save();

            return response()->json([
                'success' => true,
                'qr_image' => $fileName,
                'url' => asset('storage/qrcodes/'.$fileName)
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al guardar QR',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function crearOrden(Request $request)
    {
        $validated = $request->validate([
            'id_reserva' => 'required|integer|exists:reservas,id_reserva',
            'id_user' => 'required|integer|exists:users,iduser',
            'hora_comida' => 'required|integer|min:0|max:23',
            'id_turno' => 'required|integer|exists:turno,id_turno' // <-- nuevo
        ]);
        

        DB::beginTransaction();

        try {
            $orden = Orden::create([
                'id_user' => (int)$validated['id_user'],
                'id_reserva' => (int)$validated['id_reserva'],
                'id_turno' => (int)$validated['id_turno'], // <-- nuevo
                'fecha' => now(),
                'hora_comida' => $validated['hora_comida'],
                'status' => 1,
            ]);
            

            $reserva = Reserva::find((int)$validated['id_reserva']);
            $reserva->orden = 2;
            $reserva->status = 1; 
            $reserva->save();

            DB::commit();

            return response()->json([
                'success' => true,
                'orden' => $orden,
                'reserva' => $reserva,
                'message' => 'Orden creada exitosamente'
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Error al crear orden: '.$e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error al crear la orden',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function mostrarQR(string $nombre_foto)
    {
        $path = storage_path("app/public/qrcodes/{$nombre_foto}");

        if (!file_exists($path) || empty($nombre_foto)) {
            abort(404);
        }

        return response()->file($path);
    }

    public function ultimaReserva($userId) 
    {
        $reserva = Reserva::where('id_user', $userId)
                         ->latest('fecha_reserva')
                         ->first();

        return response()->json([
            'success' => true,
            'reserva' => $reserva
        ]);
    }

    // ======== MÉTODOS PRIVADOS ========
    private function guardarQR(Reserva $reserva, string $base64Image): void
    {
        $imageData = base64_decode(preg_replace('#^data:image/\w+;base64,#i', '', $base64Image));
        $fileName = 'qr_reserva_' . $reserva->id_reserva . '.png';
        $path = 'qrcodes/' . $fileName;
        
        Storage::disk('public')->put($path, $imageData);
        $reserva->qr_image = $fileName;
        $reserva->save();
    }

    private function eliminarQR(?string $fileName): void
    {
        if ($fileName && Storage::disk('public')->exists('qrcodes/' . $fileName)) {
            Storage::disk('public')->delete('qrcodes/' . $fileName);
        }
    }
    /**
 * Verificar y obtener orden asociada a una reserva
 * 
 * @param int $id_reserva ID de la reserva
 * @return \Illuminate\Http\JsonResponse
 */public function verOrden($id_reserva)
{
    try {
        // Buscar la orden asociada a la reserva
        $orden = Orden::where('id_reserva', $id_reserva)->first();

        if (!$orden) {
            return response()->json([
                'success' => false,
                'message' => 'No se encontró una orden asociada a esta reserva'
            ], 404);
        }

        // Traer los detalles de la orden
        $detalles = DetalleOrden::where('id_orden', $orden->id)->get();

        // Calcular subtotal normal (numero_pedido = 1)
        $subtotalNormal = $detalles
            ->where('numero_pedido', 1)
            ->sum('subtotal');

        // Calcular total extra (numero_pedido = 2)
        $totalExtra = $detalles
            ->where('numero_pedido', 2)
            ->sum('subtotal');

        return response()->json([
            'success' => true,
            'orden' => $orden,
            'detalles' => $detalles,   // todos los detalles con info de productos, status_pagado y metodo_pago
            'subtotal' => $subtotalNormal,
            'total_extra' => $totalExtra,
            'message' => 'Orden y detalles encontrados'
        ]);

    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'message' => 'Error al buscar la orden',
            'error' => $e->getMessage()
        ], 500);
    }
}


public function cambiarEstado(Request $request, $id_reserva)
{
    $request->validate([
        'status_reserva' => 'required|integer'
    ]);

    $reserva = Reserva::findOrFail($id_reserva);
    $reserva->status = $request->status_reserva;
    $reserva->save();

    return response()->json([
        'message' => 'Estado de la reserva actualizado correctamente',
        'reserva' => $reserva
    ], 200);
}

public function marcarPagado(Request $request, $id_orden)
{
    $request->validate([
        'metodo_pago' => 'required|string|max:50'
    ]);

    try {
        // Obtener todos los detalles de la orden
        $detalles = DetalleOrden::where('id_orden', $id_orden)->get();

        foreach ($detalles as $detalle) {
            $detalle->status_pagado = 2; // Pagado
            $detalle->metodo_pago = $request->metodo_pago;
            $detalle->save();
        }

        return response()->json([
            'success' => true,
            'message' => 'Pago registrado correctamente en todos los detalles',
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

public function pagarEfectivo(Request $request)
{
    $request->validate([
        'id_orden' => 'required|integer|exists:orden,id',
        'iduser' => 'required|integer'
    ]);

    try {
        // Obtener todos los detalles de la orden
        $detalles = DetalleOrden::where('id_orden', $request->id_orden)->get();

        foreach ($detalles as $detalle) {
            $detalle->status_pagado = 3; // Pendiente efectivo
            $detalle->metodo_pago = 'Efectivo';
            $detalle->save();
        }

        return response()->json([
            'success' => true,
            'message' => 'Pago en efectivo registrado correctamente en todos los detalles',
            'detalles' => $detalles
        ]);

    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'message' => 'Error al registrar pago en efectivo',
            'error' => $e->getMessage()
        ], 500);
    }
}



}