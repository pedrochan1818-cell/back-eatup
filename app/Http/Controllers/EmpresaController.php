<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Empresa;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\File;

class EmpresaController extends Controller
{
    /**
     * Mostrar la primera empresa (si existe)
     */
    public function mostrar()
{
    $empresa = Empresa::first();

    if (!$empresa) {
        return response()->json(null, 404); // <-- importante
    }

    return response()->json($empresa, 200);
}


    public function agregar(Request $request)
    {
        if (Empresa::exists()) {
            return response()->json([
                "message" => "Ya existe una empresa, use actualizar."
            ], 400);
        }
    
        $empresa = new Empresa();
        $camposPermitidos = ['nombre', 'descripcion', 'direccion', 'telefono', 'email', 'id_plan'];
    
        foreach ($camposPermitidos as $campo) {
            if ($request->has($campo)) {
                $empresa->$campo = $request->$campo;
            }
        }
    
        // Si no viene id_plan, por defecto será 0 (plan gratis)
        if (!$request->has('id_plan')) {
            $empresa->id_plan = 0;
        }
    
        $empresa->logo = 'default.jpg';
        $empresa->save();
    
        if ($request->hasFile('logo')) {
            $this->guardarLogo($request, $empresa);
        }
    
        return response()->json([
            "message" => "Empresa creada correctamente",
            "empresa" => $empresa
        ], 201);
    }
    
    public function actualizar(Request $request)
    {
        $empresa = Empresa::first();
    
        if (!$empresa) {
            return response()->json([
                "message" => "No existe ninguna empresa. Use agregar."
            ], 404);
        }
    
        $camposPermitidos = ['nombre', 'descripcion', 'direccion', 'telefono', 'email', 'id_plan'];
    
        foreach ($camposPermitidos as $campo) {
            if ($request->has($campo)) {
                $empresa->$campo = $request->$campo;
            }
        }
    
        if ($request->hasFile('logo')) {
            $this->eliminarLogo($empresa->logo);
            $this->guardarLogo($request, $empresa);
        }
    
        $empresa->save();
    
        return response()->json([
            "message" => "Empresa actualizada correctamente",
            "empresa" => $empresa
        ], 200);
    }
    

    private function guardarLogo(Request $request, Empresa $empresa): void
    {
        $archivo = $request->file('logo');
        $nombreArchivo = 'empresa_' . $empresa->id_empresa . '.' . $archivo->getClientOriginalExtension();
        $archivo->storeAs('empresas', $nombreArchivo, 'public');

        $empresa->logo = $nombreArchivo;
        $empresa->save();
    }


    private function eliminarLogo(?string $logo): void
    {
        if ($logo && $logo !== 'default.jpg' && Storage::disk('public')->exists("empresas/{$logo}")) {
            Storage::disk('public')->delete("empresas/{$logo}");
        }
    }


    public function mostrarLogo(string $nombre_logo)
    {
        $path = storage_path("app/public/empresas/{$nombre_logo}");

        if (!File::exists($path) || empty($nombre_logo)) {
            $path = storage_path("app/public/empresas/default.jpg");
        }

        return response()->file($path);
    }
    public function actualizarPlan(Request $request)
{
    $empresa = Empresa::find($request->id_empresa);

    if (!$empresa) {
        return response()->json([
            "message" => "Empresa no encontrada"
        ], 404);
    }

    if (!$request->has('id_plan')) {
        return response()->json([
            "message" => "Falta el id_plan"
        ], 400);
    }

    $empresa->id_plan = $request->id_plan;
    $empresa->save();

    return response()->json([
        "message" => "Plan actualizado correctamente",
        "empresa" => $empresa
    ], 200);
}

}
