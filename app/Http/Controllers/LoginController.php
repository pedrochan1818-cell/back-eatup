<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;

class LoginController extends Controller
{
    public function iniciar_sesion(Request $request)
    {
        $credentials = $request->only('email', 'password');

        if (Auth::attempt($credentials)) {
            $user = Auth::user();
            return response()->json([
                'message' => '¡Login exitoso!',
                'user' => $user,
                'redirect' => $this->redirectPath($user->idrol),
            ], 200);
        }

        return response()->json(['error' => 'Contraseña y/o usuario incorrectas'], 401);
    }

    private function redirectPath($idrol)
    {
        return match ($idrol) {
            1 => '/admin',
            2 => '/home',
            default => '/',
        };
    }

    public function logout()
    {
        Auth::logout();
        return response()->json(['message' => 'Sesión cerrada con éxito'], 200);
    }
}
