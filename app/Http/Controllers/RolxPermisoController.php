<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Permiso;
use App\Models\RolxPermiso;

class RolxPermisoController extends Controller
{
    // Obtener permisos asignados y no asignados a un rol
    public function obtenerPermisosPorRol($id)
    {
        // Obtener todos los permisos
        $permisos = Permiso::all();

        // Obtener los permisos ya asignados al rol
        $permisosAsignados = RolxPermiso::where('idrol', $id)->pluck('idpermiso')->toArray();

        // Marcar los permisos como asignados o no asignados
        foreach ($permisos as $permiso) {
            $permiso->asignada = in_array($permiso->idpermiso, $permisosAsignados);
        }

        return response()->json([
            'permisos' => $permisos,
            'idrol' => $id,
        ]);
    }

    // Guardar o actualizar los permisos asignados a un rol
    public function guardarPermisos(Request $request)
    {
        $datos = $request->all();

        // Borrar todos los permisos asignados al rol
        RolxPermiso::where('idrol', $datos['idrol'])->delete();

        if (isset($datos['idpermiso'])) {
            foreach ($datos['idpermiso'] as $permiso) {
                // Crear un nuevo registro en la tabla rolxpermiso
                $rxp = new RolxPermiso();
                $rxp->idrol = $datos['idrol'];
                $rxp->idpermiso = $permiso;
                $rxp->save();
            }
        }

        return response()->json(['message' => 'Permisos guardados correctamente.']);
    }
}
