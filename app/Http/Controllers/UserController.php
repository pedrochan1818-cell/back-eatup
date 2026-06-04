<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Rol;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;

class UserController extends Controller
{
    // Método para realizar operaciones sobre el usuario: agregar, modificar, eliminar
    public function operacion(Request $request)
    {
        $datos = $request->all();

        switch ($datos['operacion']) {
            case 'Agregar':
                $validator = Validator::make($datos, [
                    'nombre' => 'nullable|string|max:255',
                    'email' => 'required|email|unique:users,email',
                    'password' => 'required|min:2',
                    'idrol' => 'required|integer|exists:rol,idrol',
                    'genero' => 'nullable|in:M,F,otro',
                    'edad' => 'nullable|integer',
                    'foto' => 'nullable|image',
                ]);

                if ($validator->fails()) {
                    return response()->json(['error' => $validator->errors()], 400);
                }

                // Crear usuario sin foto aún para obtener su ID
                $user = User::create([
                    'nombre' => $datos['nombre'] ?? null,
                    'email' => $datos['email'],
                    'password' => Hash::make($datos['password']),
                    'idrol' => $datos['idrol'],
                    'genero' => $datos['genero'] ?? null,
                    'edad' => $datos['edad'] ?? null,
                    'foto' => 'default.jpg',
                ]);

                // Si el usuario sube una foto, la guardamos con su ID
                if ($request->hasFile('foto')) {
                    $this->guardarFoto($request, $user);
                }

                return response()->json(['message' => 'Usuario creado con éxito', 'user' => $user], 201);

            case 'Modificar':
                $validator = Validator::make($datos, [
                    'nombre' => 'nullable|string|max:255',
                    'email' => 'required|email|unique:users,email,' . $datos['iduser'] . ',iduser',
                    'password' => 'nullable|string|min:2',
                    'idrol' => 'required|integer|exists:rol,idrol',
                    'genero' => 'nullable|in:M,F,otro,No',
                    'edad' => 'nullable|integer',
                    'foto' => 'nullable|image',
                ]);

                if ($validator->fails()) {
                    return response()->json(['error' => $validator->errors()], 400);
                }

                $user = User::findOrFail($datos['iduser']);

                // Actualizar datos
                $user->update([
                    'nombre' => $datos['nombre'] ?? $user->nombre,
                    'email' => $datos['email'],
                    'idrol' => $datos['idrol'],
                    'genero' => $datos['genero'] ?? $user->genero,
                    'edad' => $datos['edad'] ?? $user->edad,
                    'password' => $datos['password'] ? Hash::make($datos['password']) : $user->password,
                ]);

                // Si sube una nueva foto, actualizarla
                if ($request->hasFile('foto')) {
                    $this->guardarFoto($request, $user);
                }

                return response()->json(['message' => 'Usuario actualizado con éxito', 'user' => $user]);

            case 'Eliminar':
                $user = User::findOrFail($datos['iduser']);

                // Eliminar la foto si no es la foto por defecto
                if ($user->foto !== 'default.jpg') {
                    Storage::disk('public')->delete("usuarios/{$user->foto}");
                }

                $user->delete();
                return response()->json(['message' => 'Usuario eliminado con éxito']);
        }
    }

    // Método para actualizar el perfil del usuario
 // Método para actualizar el perfil del usuario
public function operacionPerfil(Request $request)
{
    $datos = $request->all();

    // Validar los datos, sin la foto
    $validator = Validator::make($datos, [
        'nombre' => 'nullable|string|max:255',
        'email' => 'required|email|unique:users,email,' . $datos['iduser'] . ',iduser',
        'password' => 'nullable|string|min:2',
        'genero' => 'nullable|in:M,F,otro,No',
        'edad' => 'nullable|integer',
    ]);

    // Solo validar la foto si está presente en el request
    if ($request->hasFile('foto')) {
        $validator->after(function ($validator) use ($request) {
            $file = $request->file('foto');
            $allowedExtensions = ['jpeg', 'jpg', 'png', 'gif', 'webp', 'bmp', 'svg', 'ico'];
            if (!in_array($file->getClientOriginalExtension(), $allowedExtensions)) {
                $validator->errors()->add('foto', 'La foto debe ser un archivo de tipo: ' . implode(', ', $allowedExtensions));
            }
        });
    }

    if ($validator->fails()) {
        return response()->json(['error' => $validator->errors()], 400);
    }

    $user = User::findOrFail($datos['iduser']);

    // Actualizar datos del usuario
    $user->update([
        'nombre' => $datos['nombre'] ?? $user->nombre,
        'email' => $datos['email'],
        'genero' => $datos['genero'] ?? $user->genero,
        'edad' => $datos['edad'] ?? $user->edad,
        'password' => isset($datos['password']) && $datos['password'] ? Hash::make($datos['password']) : $user->password,
    ]);

    // Si se sube una foto nueva, la actualizamos
    if ($request->hasFile('foto')) {
        $this->guardarFoto($request, $user);
    }

    return response()->json(['message' => 'Usuario actualizado con éxito', 'user' => $user]);
}


    // Función para guardar la foto correctamente
    private function guardarFoto(Request $request, User $user): void
    {
        $archivo = $request->file('foto');
        $nombreArchivo = 'usuario_' . $user->iduser . '.' . $archivo->getClientOriginalExtension();

        // Eliminar la foto anterior si existe y no es la foto por defecto
        if ($user->foto !== 'default.jpg') {
            Storage::disk('public')->delete("usuarios/{$user->foto}");
        }

        // Guardar la nueva foto
        $archivo->storeAs('usuarios', $nombreArchivo, 'public');

        // Actualizar el nombre de la foto en la BD
        $user->update(['foto' => $nombreArchivo]);
    }

    // Mostrar foto del usuario
    public function mostrarFoto(string $nombre_foto)
    {
        $path = storage_path("app/public/usuarios/{$nombre_foto}");

        if (!Storage::disk('public')->exists("usuarios/{$nombre_foto}") || empty($nombre_foto)) {
            $path = storage_path("app/public/usuarios/default.jpg"); // Imagen por defecto
        }

        return response()->file($path);
    }

    // Obtener lista de usuarios con roles
    public function listado()
    {
        $users = User::join('rol', 'rol.idrol', '=', 'users.idrol')
                     ->select('users.*', 'rol.nomrol as rol')
                     ->get();

        return response()->json($users, 200);
    }
public function mostrar($iduser)
{
    $usuario = User::find($iduser);

    if (!$usuario) {
        return response()->json(['error' => 'Usuario no encontrado'], 404);
    }

    // Convertir el modelo a un array para enviar como JSON
    return response()->json($usuario->toArray());
}


}
