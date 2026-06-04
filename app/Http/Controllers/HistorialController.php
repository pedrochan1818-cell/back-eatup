<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Reserva;
use Illuminate\Support\Facades\Validator;

class HistorialController extends Controller
{
    public function listado($id_user)
    {
        // Validar que el id_user sea numérico
        if (!is_numeric($id_user)) {
            return response()->json([
                'success' => false,
                'message' => 'ID de usuario no válido'
            ], 400);
        }

        try {
            // Obtener todas las reservas del usuario ordenadas por fecha más reciente
            $reservas = Reserva::where('id_user', $id_user)
                              ->orderBy('fecha_reserva', 'desc')
                              ->get();

            return response()->json([
                'success' => true,
                'reservas' => $reservas
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener el historial',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function detalle($id_user, $id_reserva)
    {
        // Validar los IDs
        if (!is_numeric($id_user) || !is_numeric($id_reserva)) {
            return response()->json([
                'success' => false,
                'message' => 'IDs no válidos'
            ], 400);
        }

        try {
            // Obtener una reserva específica del usuario
            $reserva = Reserva::where('id_user', $id_user)
                            ->where('id_reserva', $id_reserva)
                            ->first();

            if (!$reserva) {
                return response()->json([
                    'success' => false,
                    'message' => 'Reserva no encontrada'
                ], 404);
            }

            return response()->json([
                'success' => true,
                'reserva' => $reserva
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener el detalle',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function operacion(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'operacion' => 'required|string|in:Cancelar',
            'id_user' => 'required|integer|exists:users,iduser',
            'id_reserva' => 'required|integer|exists:reservas,id_reserva'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $reserva = Reserva::where('id_reserva', $request->id_reserva)
                            ->where('id_user', $request->id_user)
                            ->first();

            if (!$reserva) {
                return response()->json([
                    'success' => false,
                    'message' => 'Reserva no encontrada o no pertenece al usuario'
                ], 404);
            }

            switch ($request->operacion) {
                case 'Cancelar':
                    // Solo actualizamos el estado de la reserva
                    $reserva->orden = 1; // 1 = solo reserva (sin orden)
                    $reserva->save();

                    return response()->json([
                        'success' => true,
                        'message' => 'Reserva cancelada exitosamente',
                        'reserva' => $reserva
                    ], 200);

                default:
                    return response()->json([
                        'success' => false,
                        'message' => 'Operación no soportada'
                    ], 400);
            }

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al procesar la operación',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}