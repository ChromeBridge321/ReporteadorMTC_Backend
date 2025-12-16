<?php
namespace App\Http;

use Illuminate\Foundation\Http\Kernel as HttpKernel;

/**
 * Kernel HTTP de la aplicación
 *
 * Define la configuración de middleware que se ejecutan en cada petición HTTP.
 * Organiza los middleware en tres categorías: globales, grupos y por ruta.
 */
class Kernel extends HttpKernel
{
    /**
     * Middleware globales de la aplicación
     *
     * Se ejecutan en TODAS las peticiones HTTP, independientemente de la ruta.
     *
     * Middleware configurados:
     * - CheckForMaintenanceMode: Verifica si la app está en modo mantenimiento
     * - Cors: Agrega headers CORS para permitir peticiones cross-origin
     *
     * @var array
     */
    protected $middleware = [
        \Illuminate\Foundation\Http\Middleware\CheckForMaintenanceMode::class,
        \App\Http\Middleware\Cors::class, // Middleware personalizado CORS
    ];

    /**
     * Grupos de middleware por tipo de ruta
     *
     * Permite agrupar middleware que se aplican a conjuntos específicos de rutas.
     *
     * - web: Rutas con interfaz web (sesiones, cookies, CSRF)
     * - api: Rutas API con throttling (limitación de peticiones)
     *
     * @var array
     */
    protected $middlewareGroups = [
        'web' => [
            \App\Http\Middleware\EncryptCookies::class,
            \Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse::class,
            \Illuminate\Session\Middleware\StartSession::class,
            \Illuminate\View\Middleware\ShareErrorsFromSession::class,
            \App\Http\Middleware\VerifyCsrfToken::class,
        ],

        'api' => [
            'throttle:60,1', // Límite: 60 peticiones por minuto
        ],
    ];

    /**
     * Middleware de rutas individuales
     *
     * Pueden ser asignados a rutas específicas según se necesite.
     * Se aplican usando ->middleware('nombre') en la definición de rutas.
     *
     * @var array
     */
    protected $routeMiddleware = [
        'auth'       => \App\Http\Middleware\Authenticate::class,                     // Requiere autenticación
        'auth.basic' => \Illuminate\Auth\Middleware\AuthenticateWithBasicAuth::class, // HTTP Basic Auth
        'can'        => \Illuminate\Foundation\Http\Middleware\Authorize::class,      // Autorización de políticas
        'guest'      => \App\Http\Middleware\RedirectIfAuthenticated::class,          // Solo para usuarios no autenticados
        'throttle'   => \Illuminate\Routing\Middleware\ThrottleRequests::class,       // Limitación de peticiones
    ];
}
