<?php
namespace App\Http\Controllers;

use App\Http\Requests\Request;
use App\Services\DatabaseConnectionService;
use Illuminate\Http\Response;

class PozoController extends Controller
{
    /**
     * Obtiene la lista de pozos desde la base de datos
     *
     * @return \Illuminate\Http\JsonResponse
     */

    private $dbConnectionService;

    public function __construct(DatabaseConnectionService $dbConnectionService)
    {
        $this->dbConnectionService = $dbConnectionService;
    }
    public function obtenerPozos(Request $request)
    {

        $nombreConexion = $request->input('Conexion');

        // Validar que la conexión esté en la lista blanca
        if (! $nombreConexion || ! $this->dbConnectionService->esConexionValida($nombreConexion)) {
            return response()->json([
                'error'                  => 'Conexión no válida',
                'conexiones_disponibles' => $this->dbConnectionService->getConexionesDisponibles(),
            ], 400);
        }
        $conexion = $this->dbConnectionService->obtenerConexion($nombreConexion);
        $sql      = $this->construirConsulta($conexion);
        try {
            return $sql;

        } catch (\Exception $e) {
            // Manejar errores y retornar respuesta de error
            return response()->json([
                'success' => false,
                'data'    => [],
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    private function construirConsulta($conexion)
    {
        $ids = [];
        if ($conexion == "bd_MTC_PozaRica") {
            $ids = [170, 168, 234, 246, 254, 262, 263];
        } elseif ($conexion == "DB1") {
            $ids = [170, 168, 234, 246, 254, 262, 263];
        } elseif ($conexion == "DB2") {
            $ids = [170, 168, 234, 246, 254, 262, 263];
        } elseif ($conexion == "DB3") {
            $ids = [170, 168, 234, 246, 254, 262, 263];
        }

        $sql = "select IdPozo, NombrePozo from [t_Instalacion.Pozos] where ";
        for ($i = 0; $i < count($ids); $i++) {
            if ($i == count($ids) - 1) {
                $sql = $sql . " IdPozo = " . $ids[$i] . " ";
                break;
            }
            $sql = $sql . " IdPozo = " . $ids[$i] . " or ";
        }
        return $sql;
    }

}
