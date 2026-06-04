<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Laravel\Socialite\Facades\Socialite;
use App\Models\User;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Hash;
use Exception;

class SocialAuthController extends Controller
{
    // Redirige a Google
    public function redirectToGoogle() {
        return Socialite::driver('google')->stateless()->redirect();
    }
}
