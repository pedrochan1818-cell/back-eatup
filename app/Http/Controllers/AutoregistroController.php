<?php 

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\File;

class AutoregistroController extends Controller 
{
    // Mostrar formulario de autoregistro
    public function formularioauto()
    {
        return view('usuario.autoregistro');
    }

    // Procesar el autoregistro
    public function autoregistro(Request $request)
    {
        // Validación de los datos con los nombres en español
        $request->validate([
            'nombre' => 'nullable|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|min:2',
            'genero' => 'nullable|in:M,F,otro',
            'edad' => 'nullable|string',
            'foto' => 'nullable|image', // Esto valida que sea una imagen
        ]);
        
        // Crear y guardar el usuario sin la foto aún
        $user = new User();
        $user->nombre = $request->nombre;
        $user->email = $request->email;
        $user->password = Hash::make($request->password);
        $user->genero = strtoupper($request->genero);
        $user->edad = $request->edad;
        $user->idrol = 2;
        $user->foto = 'default.jpg'; // Foto por defecto temporalmente
        $user->save(); // Guardamos primero para obtener el ID

        // Si el usuario sube una foto, la guardamos con el ID del usuario
        if ($request->hasFile('foto')) {
            $this->guardarFoto($request, $user);
        }

        return response()->json(["message" => "Usuario registrado"], 201);
    }

    // Guardar foto del usuario
    private function guardarFoto(Request $request, User $user): void
    {
        $archivo = $request->file('foto');
        $nombreArchivo = 'usuario_' . $user->id . '.' . $archivo->getClientOriginalExtension();
        
        // Guardamos la imagen en storage/app/public/usuarios/
        $archivo->storeAs('usuarios', $nombreArchivo, 'public');

        // Actualizamos el nombre de la foto en la BD
        $user->foto = $nombreArchivo;
        $user->save();
    }

    // Mostrar foto del usuario
    public function mostrarFoto(string $nombre_foto)
    {
        $path = storage_path("app/public/usuarios/{$nombre_foto}");

        if (!File::exists($path) || empty($nombre_foto)) {
            $path = storage_path("app/public/usuarios/default.jpg"); // Imagen por defecto
        }

        return response()->file($path);
    }
}
