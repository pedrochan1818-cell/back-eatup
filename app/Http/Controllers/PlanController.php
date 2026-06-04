<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Plan;

class PlanController extends Controller
{
    public function listado()
    {
        return response()->json(Plan::all(), 200);
    }

    public function operacion(Request $request)
    {
        $datos = $request->all();

        switch ($datos['operacion']) {
            case 'Agregar':
                $plan = new Plan();
                $plan->nombre = $datos['nombre'];
                $plan->precio = $datos['precio'];
                $plan->descripcion = $datos['descripcion'];
                $plan->beneficios = $datos['beneficios']; // string o json
                $plan->save();
                return response()->json(["message" => "Plan agregado"], 200);

            case 'Modificar':
                $plan = Plan::findOrFail($datos['id_plan']);
                $plan->nombre = $datos['nombre'];
                $plan->precio = $datos['precio'];
                $plan->descripcion = $datos['descripcion'];
                $plan->beneficios = $datos['beneficios'];
                $plan->save();
                return response()->json(["message" => "Plan actualizado"], 200);

            case 'Eliminar':
                $plan = Plan::findOrFail($datos['id_plan']);
                $plan->delete();
                return response()->json(["message" => "Plan eliminado"], 200);
        }
    }
}
