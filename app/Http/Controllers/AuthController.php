<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Laravel\Socialite\Facades\Socialite;
use App\Models\User;

class AuthController extends Controller
{
    // Redirige a Google
    public function redirectToGoogle() {
        return Socialite::driver('google')->redirect();
    }

    public function handleGoogleCallback()
{
    $googleUser = Socialite::driver('google')->user();

    // Buscar usuario por email
    $user = User::where('email', $googleUser->getEmail())->first();

    if (! $user) {
        // Crear usuario nuevo con contraseña aleatoria
        $plainPassword = \Illuminate\Support\Str::random(24);
        $user = User::create([
            'nombre' => $googleUser->getName(),
            'email' => $googleUser->getEmail(),
            'password' => bcrypt($plainPassword),
            'idrol' => 2,
            'genero' => 'No',
            'edad' => 10,
            'foto' => 'default.jpg',
        ]);
    }

    // Loguear al usuario en Laravel
    auth()->login($user);

    // Generar token (si usas Sanctum o JWT, ajusta aquí)
    $token = $user->createToken('auth_token')->plainTextToken;

    // Redirigir al frontend con parámetros en la URL
    return redirect("http://localhost:3000/googleLogin?token={$token}&iduser={$user->iduser}&email={$user->email}&nombre=" . urlencode($user->nombre));
}


}