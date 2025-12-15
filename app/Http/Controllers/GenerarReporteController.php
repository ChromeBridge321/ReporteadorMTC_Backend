<?php
namespace App\Http\Controllers;

use App\Http\Requests\PozosIdRequest;
use App\Services\DatabaseConnectionService;
use Illuminate\Support\Facades\DB;

class GenerarReporteController extends Controller
{
    private $dbConnectionService;

    public function __construct(DatabaseConnectionService $dbConnectionService)
    {
        $this->dbConnectionService = $dbConnectionService;
    }

    /**
     * Genera un reporte diario para los pozos seleccionados
     *
     * @param PozosIdRequest $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function generarReporteConexion(PozosIdRequest $request)
    {
        $nombreConexion = $request->input('Conexion');

        if (! $nombreConexion || ! $this->dbConnectionService->esConexionValida($nombreConexion)) {
            return response()->json([
                'error'                  => 'Conexión no válida',
                'conexiones_disponibles' => $this->dbConnectionService->getConexionesDisponibles(),
            ], 400);
        }

        $conexion = $this->dbConnectionService->obtenerConexion($nombreConexion);
        $pozosIDs = (array) $request->input('Pozos', []);
        $fecha    = $request->input('Fecha');

        if (empty($pozosIDs) || empty($fecha)) {
            return response()->json([], 200);
        }

        try {
            $reportePozo = $this->generarReportePorPozos($conexion, $pozosIDs, $fecha);
            return response()->json($reportePozo, 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'data'    => [],
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Genera el reporte para cada pozo
     *
     * @param string $conexion
     * @param array $pozosIDs
     * @param string $fecha
     * @return array
     */
    private function generarReportePorPozos(string $conexion, array $pozosIDs, string $fecha): array
    {
        $reportePozo = [];
        $sql         = $this->construirConsulta($conexion);

        foreach ($pozosIDs as $pozoId) {
            $consulta = DB::connection($conexion)->select($sql, [$pozoId, $fecha, $fecha]);

            if (! empty($consulta)) {
                $registros = array_map(function ($registro) {
                    return (array) $registro;
                }, $consulta);

                $reportePozo[] = [
                    'nombrePozo' => $consulta[0]->Pozo,
                    'reporte'    => 'Diario',
                    'registros'  => $registros,
                ];
            }
        }

        return $reportePozo;
    }

    /**
     * Construye la consulta SQL para obtener los datos del reporte
     *
     * @param string $dbName
     * @return string
     */
    private function construirConsulta(string $dbName): string
    {
        return "
            WITH Horas AS (
                SELECT n AS Hora
                FROM (
                    SELECT ROW_NUMBER() OVER (ORDER BY (SELECT NULL)) - 1 AS n
                    FROM master..spt_values
                ) AS T
                WHERE n BETWEEN 0 AND 23
            ),
            Promedios AS (
                SELECT
                    IP.NombrePozo AS Pozo,
                    DATEPART(HOUR, VH.Fecha) AS Hora,
                    AVG(CASE WHEN PT.Nombre = 'PRESION_TP' THEN VH.Valor END) AS PresionTP,
                    AVG(CASE WHEN PT.Nombre = 'PRESION_TR' THEN VH.Valor END) AS PresionTR,
                    AVG(CASE WHEN PT.Nombre = 'PRESION_LE' THEN VH.Valor END) AS LDD,
                    AVG(CASE WHEN PT.Nombre = 'TEMP_LE' THEN VH.Valor END) AS TempPozo,
                    AVG(CASE WHEN PT.Nombre = 'PRESION_SUCCION' THEN VH.Valor END) AS PresionSuccion,
                    AVG(CASE WHEN PT.Nombre = 'PRESION_ESTATICA_DESCARGA' THEN VH.Valor END) AS PresionEstDesc,
                    AVG(CASE WHEN PT.Nombre = 'VELOCIDAD' THEN VH.Valor END) AS Velocidad,
                    AVG(CASE WHEN PT.Nombre = 'TEMPERATURA_DESCARGA' THEN VH.Valor END) AS TempDesc,
                    AVG(CASE WHEN PT.Nombre = 'TEMPERATURA_SUCCION' THEN VH.Valor END) AS TempSuccion,
		            AVG(CASE WHEN PT.Nombre = 'FLUJO_CORREGIDO_DESCARGA' THEN VH.Valor END) AS Qiny
                FROM [$dbName].[dbo].[t_Historicos.ValoresTags] VH
                INNER JOIN [t_Instalacion.Pozos] IP ON IP.IdPozo = VH.IdPozo
                INNER JOIN [t_Proceso.Tags] PT ON PT.IdTag = VH.IdTag
                WHERE VH.IdPozo = ?
                  AND VH.Fecha >= ?
                  AND VH.Fecha < DATEADD(DAY, 1, ?)
                GROUP BY DATEPART(HOUR, VH.Fecha), IP.NombrePozo
            )
            SELECT
                ISNULL(P.Pozo, 'Sin Datos') AS Pozo,
                H.Hora,
                RIGHT('00' + CAST(H.Hora AS VARCHAR(2)), 2) + ':00' AS Hora_Formato,
                ROUND(ISNULL(P.PresionTP, 0), 1) AS [Presion_TP],
                ROUND(ISNULL(P.PresionTR, 0), 1) AS [Presion_TR],
                ROUND(ISNULL(P.LDD, 0), 1) AS [LDD],
                ROUND(ISNULL(P.TempPozo, 0), 1) AS [Temperatura_Pozo],
                ROUND(ISNULL(P.PresionSuccion, 0), 1) AS [Presion_Succion],
                ROUND(ISNULL(P.PresionEstDesc, 0), 1) AS [Presion_Descarga],
                ROUND(ISNULL(P.Velocidad, 0), 1) AS [Velocidad],
                ROUND(ISNULL(P.TempDesc, 0), 1) AS [Temp_Descarga],
                ROUND(ISNULL(P.TempSuccion, 0), 1) AS [Temp_Succion],
	            ROUND(ISNULL(P.Qiny, 0), 1) AS [Qiny]
            FROM Horas H
            LEFT JOIN Promedios P ON H.Hora = P.Hora
            ORDER BY H.Hora
        ";
    }
}
