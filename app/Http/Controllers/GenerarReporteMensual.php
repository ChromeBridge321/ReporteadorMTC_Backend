<?php
namespace App\Http\Controllers;

use App\Http\Requests\PozosIdRequest;
use App\Services\DatabaseConnectionService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * Controlador para generar reportes mensuales de pozos
 *
 * Este controlador genera reportes consolidados con promedios diarios
 * de diferentes parámetros operacionales para todo un mes.
 *
 * IMPORTANTE - COMPATIBILIDAD SQL SERVER 2008:
 * - No usa prepared statements debido a limitaciones de SQL Server 2008
 * - Construye consultas con valores embebidos directamente
 * - Incluye manejo de errores robusto para continuar con otros pozos si uno falla
 */
class GenerarReporteMensual extends Controller
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
     * Genera un reporte mensual consolidado para los pozos seleccionados
     *
     * Crea un reporte con promedios diarios de todos los parámetros operacionales
     * durante un mes completo. Incluye todos los días del mes aunque no tengan datos.
     *
     * Formato de fecha esperado: 'YYYY-MM' (ejemplo: '2025-12')
     *
     * @param PozosIdRequest $request Request validado con: Conexion (string), Pozos (array de IDs), Fecha (mes YYYY-MM)
     * @return \Illuminate\Http\JsonResponse Array de objetos con nombrePozo, reporte y registros diarios del mes
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
     * Genera el reporte mensual para cada pozo en la lista
     *
     * Itera sobre cada ID de pozo, ejecuta la consulta SQL y estructura
     * los resultados con todos los días del mes especificado.
     *
     * MANEJO DE ERRORES:
     * - Si un pozo falla, registra el error en logs y continúa con los demás
     * - Permite obtener datos parciales aunque algunos pozos fallen
     * - Útil para sistemas con múltiples pozos de diferentes estabilidades
     *
     * COMPATIBILIDAD SQL SERVER 2008:
     * - Usa DB::raw() para ejecutar consultas sin prepared statements
     * - Construye SQL con valores embebidos directamente en la consulta
     *
     * @param string $conexion Nombre de la conexión a la base de datos
     * @param array $pozosIDs Array de IDs de pozos a consultar
     * @param string $mes Mes del reporte en formato YYYY-MM (ejemplo: '2025-12')
     * @return array Array estructurado con nombrePozo, tipo de reporte y registros diarios del mes
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
     * Construye la consulta SQL completa con valores embebidos para el reporte mensual
     *
     * ESTRUCTURA DE LA CONSULTA:
     * 1. SET LANGUAGE Spanish: Configura nombres de días en español
     * 2. Variables: Calcula fecha inicio y fin del mes
     * 3. CTE 'Dias': Genera recursivamente todos los días del mes
     * 4. CTE 'Promedios': Calcula promedios diarios de parámetros por día
     * 5. SELECT final: LEFT JOIN para incluir días sin datos, con formato de fecha
     *
     * PARÁMETROS INCLUIDOS:
     * - Presiones: TP, TR, Succión, Descarga Estática
     * - Temperaturas: Pozo, Descarga, Succión
     * - Operacionales: Velocidad, LDD (Nivel Dinámico), Qiny (Flujo)
     *
     * NOTA IMPORTANTE:
     * - Valores embebidos directamente (no prepared statements)
     * - Uso de DB::raw() requerido para SQL Server 2008
     * - MAXRECURSION 0: Permite CTE recursivo sin límite de iteraciones
     *
     * @param string $dbName Nombre de la base de datos para construir la ruta completa de las tablas
     * @param int $pozoId ID del pozo a consultar (valor embebido en la consulta)
     * @param string $mes Mes en formato YYYY-MM (valor embebido en la consulta)
     * @return string Query SQL completo con valores embebidos
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
