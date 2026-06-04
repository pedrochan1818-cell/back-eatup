<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Producto;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\File;

class ProductoController extends Controller 
{
    public function listado()
    {
        // Devolver todos los productos, incluyendo descripción y categoría
        return response()->json(Producto::all(), 200);
    }

    public function operacion(Request $request)
    {
        $datos = $request->all();

        switch ($datos['operacion']) {
            case 'Agregar':
                $producto = new Producto();
                $producto->nombre = $datos['nombre'];
                $producto->precio = $datos['precio'];
                $producto->descripcion = $datos['descripcion'];
                $producto->categoria = $datos['categoria'] ?? 'comida'; // ✅ Nuevo campo
                $producto->foto = 'default.jpg';
                $producto->save(); // Guardamos primero para obtener el ID

                if ($request->hasFile('foto')) {
                    $this->guardarFoto($request, $producto);
                }

                return response()->json(["message" => "Producto agregado"], 200);

            case 'Modificar':
                $producto = Producto::findOrFail($datos['idproducto']);
                $producto->nombre = $datos['nombre'];
                $producto->precio = $datos['precio'];
                $producto->descripcion = $datos['descripcion'];
                $producto->categoria = $datos['categoria'] ?? $producto->categoria; // ✅ Actualiza categoría

                if ($request->hasFile('foto')) {
                    $this->eliminarFoto($producto->foto);
                    $this->guardarFoto($request, $producto);
                }

                $producto->save();
                return response()->json(["message" => "Producto actualizado"], 200);

            case 'Eliminar':
                $producto = Producto::findOrFail($datos['idproducto']);
                $this->eliminarFoto($producto->foto);
                $producto->delete();
                return response()->json(["message" => "Producto eliminado"], 200);
        }
    }

    private function guardarFoto(Request $request, Producto $producto): void
    {
        $archivo = $request->file('foto');
        $nombreArchivo = 'producto_' . $producto->idproducto . '.' . $archivo->getClientOriginalExtension();
        
        $archivo->storeAs('productos', $nombreArchivo, 'public');
        
        $producto->foto = $nombreArchivo;
        $producto->save();
    }

    private function eliminarFoto(?string $foto): void
    {
        if ($foto && $foto !== 'default.jpg' && Storage::disk('public')->exists("productos/{$foto}")) {
            Storage::disk('public')->delete("productos/{$foto}");
        }
    }

    public function mostrarFoto(string $nombre_foto)
    {
        $path = storage_path("app/public/productos/{$nombre_foto}");

        if (!File::exists($path) || empty($nombre_foto)) {
            $path = storage_path("app/public/productos/default.jpg");
        }

        return response()->file($path);
    }
}
