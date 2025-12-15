<?php
namespace App\Http\Controllers;

use App\Http\Requests\PozosIdRequest;
use App\Services\DatabaseConnectionService;
use Illuminate\Support\Facades\DB;

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

    private function construirConsulta($ids)
    {
        if (empty($ids)) {
            return "select IdPozo, NombrePozo from [t_Instalacion.Pozos] where 1=0";
        }

        $idsStr = implode(',', $ids);
        return "select IdPozo, NombrePozo from [t_Instalacion.Pozos] where IdPozo IN ($idsStr)";
    }

    private function cargarIdsPozos($conexion)
    {
        $consulta = DB::connection($conexion)->select("select COUNT(IdPozo) as ID, IdPozo from [t_Historicos.ValoresTags] group by IdPozo");
        $ids      = array_map(function ($item) {
            return (int) $item->IdPozo;
        }, $consulta);
        return $ids;
    }

}
