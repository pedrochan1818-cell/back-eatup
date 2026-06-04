<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Mesa;

class MesaController extends Controller
{
    // Obtener todas las mesas
    public function obtenerMesas()
    {
        return response()->json(Mesa::all());
    }

    public function operarMesa(Request $request)
    {
        $operacion = $request->operacion ?? null;

        switch ($operacion) {

            case 'Agregar':
                $mesa = Mesa::create([
                    'nombre' => $request->nombre ?? 'Mesa ' . (Mesa::count() + 1),
                    'x' => $request->x ?? 50,
                    'y' => $request->y ?? 50,
                    'estado' => $request->estado ?? 'disponible',
                    'asientos' => $request->filled('asientos') ? $request->asientos : 1,
                    'descripcion' => $request->descripcion ?? null,
                    'area' => $request->area ?? 'general', 
                ]);
                return response()->json(['message' => 'Mesa agregada con éxito', 'mesa' => $mesa], 201);


            case 'Editar':
                $mesa = Mesa::findOrFail($request->id);
                $mesa->update([
                    'nombre' => $request->nombre ?? $mesa->nombre,
                    'estado' => $request->estado ?? $mesa->estado,
                    'asientos' => $request->asientos ?? $mesa->asientos,
                    'descripcion' => $request->descripcion ?? $mesa->descripcion,
                    'area' => $request->area ?? $mesa->area, 
                ]);
                return response()->json(['message' => 'Mesa actualizada con éxito', 'mesa' => $mesa], 200);


            case 'Eliminar':
                $mesa = Mesa::findOrFail($request->id);
                $mesa->delete();
                return response()->json(['message' => 'Mesa eliminada con éxito'], 200);


            case 'Guardar_posiciones':
                foreach ($request->mesas as $mesa) {
                    Mesa::updateOrCreate(
                        ['id' => $mesa['id']],
                        [
                            'x' => $mesa['x'],
                            'y' => $mesa['y'],
                            'estado' => $mesa['estado'] ?? 'disponible',
                        ]
                    );
                }
                return response()->json(['message' => 'Posiciones guardadas con éxito'], 200);


            default:
                return response()->json(['error' => 'Acción no válida'], 400);
        }
    }
}
