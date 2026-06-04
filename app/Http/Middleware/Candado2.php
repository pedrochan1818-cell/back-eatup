<?php
namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class Candado2
{
    public function handle($request, Closure $next, $clave)
    {
        $usuario = Auth::user();
    
        if (!$usuario) {
            return response()->json(['error' => 'No autenticado'], 401);
        }
    
        $permiso = DB::table('users')
            ->join('rol', 'rol.idrol', '=', 'users.idrol')
            ->join('rolxpermiso', 'rol.idrol', '=', 'rolxpermiso.idrol')
            ->join('permiso', 'rolxpermiso.idpermiso', '=', 'permiso.idpermiso')
            ->where('users.iduser', $usuario->iduser)
            ->where('permiso.cvpermiso', $clave)
            ->exists();
    
        if (!$permiso) {
            return response()->json(['error' => 'No tienes permiso'], 403);
        }
    
        return $next($request);
    }
    
}
