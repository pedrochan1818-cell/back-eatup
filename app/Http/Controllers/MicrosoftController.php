<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Laravel\Socialite\Facades\Socialite;
use App\Models\User;
use Illuminate\Support\Str;

class MicrosoftController extends Controller
{
    public function redirect()
    {
        return Socialite::driver('microsoft')->redirect();
    }

    public function callback()
    {
        try {
            $microsoftUser = Socialite::driver('microsoft')->user();

            // Buscar usuario por email
            $user = User::where('email', $microsoftUser->getEmail())->first();

            if (!$user) {
                // Crear usuario nuevo con contraseña aleatoria
                $plainPassword = Str::random(24);
                $user = User::create([
                    'nombre'   => $microsoftUser->getName(),
                    'email'    => $microsoftUser->getEmail(),
                    'password' => bcrypt($plainPassword),
                    'idrol'    => 2,
                    'genero'   => 'No',
                    'edad'     => 10,
                    'foto'     => 'default.jpg',
                ]);
            }

            // Loguear al usuario en Laravel
            auth()->login($user);

            // Generar token (usando Sanctum o Passport)
            $token = $user->createToken('auth_token')->plainTextToken;

            // Redirigir al frontend Home con datos del usuario
            return redirect("http://localhost:3000/microsoftLogin?token={$token}&iduser={$user->iduser}&email={$user->email}&nombre=" . urlencode($user->nombre));


        } catch (\Exception $e) {
            
        }
    }
}
