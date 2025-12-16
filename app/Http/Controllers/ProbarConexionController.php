<?php
namespace App\Http\Controllers;

use App\Http\Requests\Request;
use App\Services\DatabaseConnectionService;

/**
 * Controlador para verificar conexiones disponibles
 *
 * Proporciona endpoints para listar las conexiones de bases de datos
 * configuradas y disponibles en el sistema.
 */
class ProbarConexionController extends Controller
{
    /**
     * Servicio para manejar conexiones dinámicas a múltiples bases de datos
     *
     * @var DatabaseConnectionService
     */
    private $dbConnectionService;

    /**
     * Constructor del controlador
     *
     * @param DatabaseConnectionService $dbConnectionService Servicio de conexiones de BD
     */
    public function __construct(DatabaseConnectionService $dbConnectionService)
    {
        $this->dbConnectionService = $dbConnectionService;
    }

    /**
     * Obtiene la lista de conexiones disponibles en el sistema
     *
     * Útil para que el frontend conozca qué bases de datos están configuradas
     * y pueda presentarlas al usuario como opciones.
     *
     * @param Request $request Request HTTP (no requiere parámetros)
     * @return \Illuminate\Http\JsonResponse Array con las conexiones disponibles
     */
    public function probarConexion(Request $request)
    {
        $conexiones = $this->dbConnectionService->getConexionesDisponibles();
        return response()->json(['conexiones_disponibles' => $conexiones]);
    }
}
