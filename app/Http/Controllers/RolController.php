<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Rol;

class RolController extends Controller 
{
    public function index() {
        return response()->json(Rol::all());
    }
    public function operacion(Request $request)
    {
        $datos = $request->all();

        switch ($datos['operacion']) {
            case 'Agregar':
                $rol = new Rol();
                $rol->nomrol = $datos['nomrol'];
                $rol->save();
                return response()->json(["message" => "Rol agregado"], 200);

            case 'Modificar':
                $rol = Rol::findOrFail($datos['idrol']);
                $rol->nomrol = $datos['nomrol'];
                $rol->save();
                return response()->json(["message" => "Rol actualizado"], 200);

            case 'Eliminar':
                $rol = Rol::findOrFail($datos['idrol']);
                \App\Models\RolxPermiso::where('idrol', $rol->idrol)->delete();
                $rol->delete();
                return response()->json(["message" => "Rol y sus permisos eliminados correctamente"], 200);
                
        }
    }
}
