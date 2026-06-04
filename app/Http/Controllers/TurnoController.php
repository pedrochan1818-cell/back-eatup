<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Turno;
use Illuminate\Support\Facades\DB;

class TurnoController extends Controller
{
    public function listado()
    {
        $turnos = Turno::all(); // devuelve todos los turnos
        return response()->json($turnos, 200); // aunque esté vacío, es un array []
    }

    
    public function status_dos(Request $request)
    {
        $id_empresa = $request->id_empresa;
    
        // Busca el primer turno con status = 2
        $turno = Turno::where('id_empresa', $id_empresa)
                      ->where('status', 2)
                      ->first();
    
        if ($turno) {
            // Si existe, devuelve toda la información del turno
            return response()->json($turno, 200);
        } else {
            // Si no existe, devuelve solo 1
            return response()->json(1, 200);
        }
    }
    
    
    public function operacion(Request $request)
    {
        $datos = $request->all();

        switch ($datos['operacion']) {
            case 'Agregar':
                $turno = Turno::create([
                    'id_empresa' => $datos['id_empresa'] ?? null,
                    'nombre' => $datos['nombre'],
                    'hora_inicio' => $datos['hora_inicio'],
                    'hora_fin' => $datos['hora_fin'],
                    'status' => 1, // por defecto inactivo
                ]);
                return response()->json(["message" => "Turno agregado", "data" => $turno], 200);

            case 'Modificar':
                $turno = Turno::findOrFail($datos['id_turno']);
                $turno->update([
                    'nombre' => $datos['nombre'],
                    'hora_inicio' => $datos['hora_inicio'],
                    'hora_fin' => $datos['hora_fin'],
                ]);
                return response()->json(["message" => "Turno actualizado"], 200);

            case 'Eliminar':
                $turno = Turno::findOrFail($datos['id_turno']);
                $turno->delete();
                return response()->json(["message" => "Turno eliminado"], 200);

            case 'Activar':
                // desactivar todos los turnos de la misma empresa
                Turno::where('id_empresa', $datos['id_empresa'])->update(['status' => 1]);
                // activar el turno seleccionado
                $turno = Turno::findOrFail($datos['id_turno']);
                $turno->status = 2;
                $turno->save();
                return response()->json(["message" => "Turno activado"], 200);

            case 'Corte':
                    DB::table('turno')
                        ->where('id_empresa', $request->id_empresa)
                        ->update(['status' => 1]);
                    return response()->json(['message' => 'Corte realizado correctamente'], 200);
                
        }
    }
}
