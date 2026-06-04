<?php

namespace App\Http\Controllers;

use Firebase\JWT\JWT;
use Illuminate\Http\Request;

class MetabaseController extends Controller
{
    private $METABASE_SITE_URL = "http://localhost:3000";
    private $METABASE_SECRET_KEY = "3115495fad6e0aa5a5585b4f349d2088856eb38b062cb8fb9633754cdbae9f91";

    // Pregunta 80 → Horarios usados
    public function horarios_usados()
    {
        $payload = [
            "resource" => ["question" => 80],
            "params" => new \stdClass(),
            "exp" => time() + (10 * 60)
        ];

        $token = JWT::encode($payload, $this->METABASE_SECRET_KEY, 'HS256');
        $iframeUrl = $this->METABASE_SITE_URL . "/embed/question/" . $token . "#bordered=true&titled=true";

        return response()->json(['url' => $iframeUrl]);
    }

    // Pregunta 81 → Distribución por género
    public function distribucion_genero()
    {
        $payload = [
            "resource" => ["question" => 81],
            "params" => new \stdClass(),
            "exp" => time() + (10 * 60)
        ];

        $token = JWT::encode($payload, $this->METABASE_SECRET_KEY, 'HS256');
        $iframeUrl = $this->METABASE_SITE_URL . "/embed/question/" . $token . "#bordered=true&titled=true";

        return response()->json(['url' => $iframeUrl]);
    }

    public function distribucion_edad()
    {
        $payload = [
            "resource" => ["question" => 83],
            "params" => new \stdClass(),
            "exp" => time() + (10 * 60)
        ];

        $token = JWT::encode($payload, $this->METABASE_SECRET_KEY, 'HS256');
        $iframeUrl = $this->METABASE_SITE_URL . "/embed/question/" . $token . "#bordered=true&titled=true";

        return response()->json(['url' => $iframeUrl]);
    }
    public function distribucion_orden()
    {
        $payload = [
            "resource" => ["question" => 82],
            "params" => new \stdClass(),
            "exp" => time() + (10 * 60)
        ];

        $token = JWT::encode($payload, $this->METABASE_SECRET_KEY, 'HS256');
        $iframeUrl = $this->METABASE_SITE_URL . "/embed/question/" . $token . "#bordered=true&titled=true";

        return response()->json(['url' => $iframeUrl]);
    }
    public function distribucion_mesa(Request $request)
    {
        // Tomamos los filtros que envíe React (por ejemplo: fecha_inicio y mesa_id)
        $params = new \stdClass();
        if ($request->has('fecha_inicio')) {
            $params->fecha_inicio = $request->input('fecha_inicio');
        }
        if ($request->has('mesa_id')) {
            $params->mesa_id = $request->input('mesa_id');
        }
    
        $payload = [
            "resource" => ["dashboard" => 3], // ID del dashboard
            "params" => $params,
            "exp" => time() + (10 * 60) // expira en 10 minutos
        ];
    
        $token = \Firebase\JWT\JWT::encode($payload, $this->METABASE_SECRET_KEY, 'HS256');
    
        $iframeUrl = $this->METABASE_SITE_URL . "/embed/dashboard/" . $token . "#bordered=true&titled=true";
    
        return response()->json(['url' => $iframeUrl]);
    }
    
    

}
