<?php
namespace App\Http\Middleware;

use Closure;

class Cors
{
    public function handle($request, Closure $next)
    {
        $response = $next($request);
        //local
        $response->headers->set('Access-Control-Allow-Origin', 'http://localhost:3000');
        //web
        //$response->headers->set('Access-Control-Allow-Origin', 'https://eatup-front.vercel.app');
        $response->headers->set('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, OPTIONS');
        $response->headers->set('Access-Control-Allow-Headers', 'Content-Type, Authorization');
        $response->headers->set('Access-Control-Allow-Credentials', 'true');
        return $response;
    }
}
