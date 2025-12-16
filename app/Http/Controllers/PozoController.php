<?php
namespace App\Http\Controllers;

use App\Http\Requests\PozosIdRequest;
use App\Services\DatabaseConnectionService;
use Illuminate\Support\Facades\DB;

/**
 * Controlador para gestionar operaciones relacionadas con pozos
 *
 * Proporciona endpoints para obtener información de pozos disponibles
 * en las diferentes bases de datos configuradas.
 */
class PozoController extends Controller
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
     * Obtiene la lista de pozos activos desde la base de datos especificada
     *
     * Filtra solo los pozos que tienen registros históricos en la tabla
     * t_Historicos.ValoresTags para asegurar que son pozos con datos.
     *
     * @param PozosIdRequest $request Request con el parámetro 'Conexion'
     * @return \Illuminate\Http\JsonResponse Array de objetos con IdPozo y NombrePozo
     */
    public function obtenerPozos(PozosIdRequest $request)
    {
        $nombreConexion = $request->input('Conexion');
        if (! $nombreConexion || ! $this->dbConnectionService->esConexionValida($nombreConexion)) {
            return response()->json([
                'error'                  => 'Conexión no válida',
                'conexiones_disponibles' => $this->dbConnectionService->getConexionesDisponibles(),
            ], 400);
        }

        $conexion = $this->dbConnectionService->obtenerConexion($nombreConexion);

        try {
            $ids   = $this->cargarIdsPozos($conexion);
            $sql   = $this->construirConsulta($ids);
            $pozos = DB::connection($conexion)->select($sql);

            return response()->json($pozos, 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'data'    => [],
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Construye la consulta SQL para obtener pozos filtrados por IDs
     *
     * Si no hay IDs, retorna una consulta que no devuelve resultados (where 1=0).
     * De lo contrario, construye un IN con los IDs proporcionados.
     *
     * @param array $ids Array de IDs de pozos a incluir en la consulta
     * @return string Query SQL para seleccionar pozos
     */
    private function construirConsulta($ids)
    {
        if (empty($ids)) {
            return "select IdPozo, NombrePozo from [t_Instalacion.Pozos] where 1=0";
        }

        $idsStr = implode(',', $ids);
        return "select IdPozo, NombrePozo from [t_Instalacion.Pozos] where IdPozo IN ($idsStr)";
    }

    /**
     * Carga los IDs de pozos que tienen datos históricos
     *
     * Consulta la tabla de históricos para obtener solo los pozos que
     * tienen registros de valores, evitando pozos sin datos.
     *
     * @param string $conexion Nombre de la conexión a la base de datos
     * @return array Array de IDs de pozos (como enteros)
     */
    private function cargarIdsPozos($conexion)
    {
        $consulta = DB::connection($conexion)->select("select COUNT(IdPozo) as ID, IdPozo from [t_Historicos.ValoresTags] group by IdPozo");
        $ids      = array_map(function ($item) {
            return (int) $item->IdPozo;
        }, $consulta);
        return $ids;
    }

}
