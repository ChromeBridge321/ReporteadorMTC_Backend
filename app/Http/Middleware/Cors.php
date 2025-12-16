<?php
namespace App\Http\Middleware;

use Closure;

/**
 * Middleware CORS (Cross-Origin Resource Sharing)
 *
 * Permite que el frontend (generalmente en un dominio diferente) pueda
 * realizar peticiones HTTP a esta API sin ser bloqueado por el navegador.
 *
 * Configuración actual: Permite peticiones desde cualquier origen (*)
 * NOTA: En producción, se recomienda restringir a dominios específicos.
 */
class Cors
{
    /**
     * Maneja una petición HTTP entrante y agrega headers CORS
     *
     * Funcionamiento:
     * 1. Si es una petición OPTIONS (preflight), responde inmediatamente con headers CORS
     * 2. Para otras peticiones, continúa el flujo normal y agrega headers a la respuesta
     *
     * Headers configurados:
     * - Access-Control-Allow-Origin: Permite cualquier origen (*)
     * - Access-Control-Allow-Methods: Métodos HTTP permitidos
     * - Access-Control-Allow-Headers: Headers que el cliente puede enviar
     * - Access-Control-Max-Age: Tiempo de cacheo de la respuesta preflight (24 horas)
     *
     * @param  \Illuminate\Http\Request  $request Petición HTTP entrante
     * @param  \Closure  $next Siguiente middleware en la cadena
     * @return mixed Respuesta HTTP con headers CORS agregados
     */
    public function handle($request, Closure $next)
    {
        // Manejar petición preflight OPTIONS (verifica permisos antes de la petición real)
        if ($request->isMethod('OPTIONS')) {
            return response()->json([], 200)
                ->header('Access-Control-Allow-Origin', '*')
                ->header('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, OPTIONS')
                ->header('Access-Control-Allow-Headers', 'X-Requested-With, Content-Type, X-Token-Auth, Authorization, Accept, Origin')
                ->header('Access-Control-Max-Age', '86400'); // 24 horas en segundos
        }

        // Para peticiones normales, continuar el flujo y agregar headers CORS a la respuesta
        return $next($request)
            ->header('Access-Control-Allow-Origin', '*')
            ->header('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, OPTIONS')
            ->header('Access-Control-Allow-Headers', 'X-Requested-With, Content-Type, X-Token-Auth, Authorization, Accept, Origin');
    }
}
