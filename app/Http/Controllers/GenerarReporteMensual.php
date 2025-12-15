<?php
namespace App\Http\Controllers;

use App\Http\Requests\PozosIdRequest;
use App\Services\DatabaseConnectionService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class GenerarReporteMensual extends Controller
{
    private $dbConnectionService;

    public function __construct(DatabaseConnectionService $dbConnectionService)
    {
        $this->dbConnectionService = $dbConnectionService;
    }

    /**
     * Genera un reporte mensual para los pozos seleccionados
     *
     * @param PozosIdRequest $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function generarReporteMensual(PozosIdRequest $request)
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
        $mes      = $request->input('Fecha');

        if (empty($pozosIDs) || empty($mes)) {
            return response()->json([], 200);
        }

        try {
            $reporteMensual = $this->generarReportePorPozos($conexion, $pozosIDs, $mes);
            return response()->json($reporteMensual, 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'data'    => [],
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Genera el reporte mensual para cada pozo con compatibilidad SQL Server 2008
     *
     * @param string $conexion
     * @param array $pozosIDs
     * @param string $mes
     * @return array
     */
    private function generarReportePorPozos(string $conexion, array $pozosIDs, string $mes): array
    {
        $reporteMensual = [];

        foreach ($pozosIDs as $pozoId) {
            try {
                // Construir la consulta completa sin placeholders para SQL Server 2008
                $sql = $this->construirConsultaCompleta($conexion, $pozoId, $mes);

                // Ejecutar sin prepared statements usando DB::raw
                $consulta = DB::connection($conexion)->select(DB::raw($sql));

                if (! empty($consulta)) {
                    $registros = array_map(function ($registro) {
                        return (array) $registro;
                    }, $consulta);

                    $reporteMensual[] = [
                        'nombrePozo' => $consulta[0]->Pozo,
                        'reporte'    => 'Mensual',
                        'registros'  => $registros,
                    ];
                }
            } catch (\Exception $e) {
                // Registrar el error pero continuar con los demás pozos
                Log::error("Error en pozo {$pozoId} (mensual): " . $e->getMessage());
                continue;
            }
        }

        return $reporteMensual;
    }

    /**
     * Construye la consulta SQL completa con valores embebidos para reporte mensual
     *
     * @param string $dbName
     * @param int $pozoId
     * @param string $mes
     * @return string
     */
    private function construirConsultaCompleta(string $dbName, int $pozoId, string $mes): string
    {
        return "
            SET LANGUAGE Spanish;

            DECLARE @Mes VARCHAR(7) = '{$mes}';
            DECLARE @FechaInicio DATE = CAST(@Mes + '-01' AS DATE);
            DECLARE @FechaFin DATE = DATEADD(DAY, -1, DATEADD(MONTH, 1, @FechaInicio));

            WITH Dias AS (
                SELECT @FechaInicio AS Fecha
                UNION ALL
                SELECT DATEADD(DAY, 1, Fecha)
                FROM Dias
                WHERE Fecha < @FechaFin
            ),
            Promedios AS (
                SELECT
                    IP.NombrePozo AS Pozo,
                    CONVERT(DATE, VH.Fecha) AS Fecha,
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
                FROM [{$dbName}].[dbo].[t_Historicos.ValoresTags] VH
                INNER JOIN [t_Instalacion.Pozos] IP ON IP.IdPozo = VH.IdPozo
                INNER JOIN [t_Proceso.Tags] PT ON PT.IdTag = VH.IdTag
                WHERE VH.IdPozo = {$pozoId}
                  AND CONVERT(DATE, VH.Fecha) BETWEEN @FechaInicio AND @FechaFin
                GROUP BY CONVERT(DATE, VH.Fecha), IP.NombrePozo
            )
            SELECT
                ISNULL(P.Pozo, 'Sin Datos') AS Pozo,
                D.Fecha,
                RIGHT('00' + CAST(DAY(D.Fecha) AS VARCHAR(2)), 2) + '/' +
                RIGHT('00' + CAST(MONTH(D.Fecha) AS VARCHAR(2)), 2) + '/' +
                CAST(YEAR(D.Fecha) AS VARCHAR(4)) AS Fecha_Formato,
                DATENAME(WEEKDAY, D.Fecha) AS Dia_Semana,
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
            FROM Dias D
            LEFT JOIN Promedios P ON D.Fecha = P.Fecha
            ORDER BY D.Fecha
            OPTION (MAXRECURSION 0);
        ";
    }
}
