<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Horario;

class HorarioController extends Controller
{
    public function listado()
    {
        $horarios = Horario::first();

        if (!$horarios) {
            return response()->json(null, 404); // <-- importante
        }
    
        return response()->json($horarios, 200);
    }

    public function operacion(Request $request)
    {
        $datos = $request->all();

        switch ($datos['operacion']) {
            case 'Agregar':
                $horario = Horario::create([
                    'id_empresa' => $datos['id_empresa'],
                    'dia_semana' => $datos['dia_semana'],
                    'id_turno' => $datos['id_turno']
                ]);
                return response()->json(["message" => "Horario agregado", "data" => $horario], 200);

            case 'Modificar':
                $horario = Horario::findOrFail($datos['id_horario']);
                $horario->update([
                    'id_empresa' => $datos['id_empresa'],
                    'dia_semana' => $datos['dia_semana'],
                    'id_turno' => $datos['id_turno']
                ]);
                return response()->json(["message" => "Horario actualizado"], 200);

            case 'Eliminar':
                $horario = Horario::findOrFail($datos['id_horario']);
                $horario->delete();
                return response()->json(["message" => "Horario eliminado"], 200);
        }
    }
}
