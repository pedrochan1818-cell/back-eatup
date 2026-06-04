<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Carrito;
use Illuminate\Support\Facades\DB;

class CarritoController extends Controller
{
    // Crear Carrito
    public function crearCarrito(Request $request)
    {
        $validated = $request->validate([
            'id_user' => 'required|integer|exists:users,iduser',
            'hora_comida' => 'required|integer|min:0|max:23',
            'id_turno' => 'required|integer|exists:turno,id_turno'
        ]);

        DB::beginTransaction();

        try {
            $carrito = Carrito::create([
                'id_user' => (int) $validated['id_user'],
                'id_turno' => (int) $validated['id_turno'],
                'fecha' => now(),
                'hora_comida' => $validated['hora_comida'],
                'status' => 1,
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'carrito' => $carrito,   // 👈 AHORA SÍ EXISTE "carrito"
                'id_carrito' => $carrito->id, // 👈 lo mando directo
                'message' => 'Carrito creado exitosamente'
            ]);

        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => 'Error al crear carrito',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
