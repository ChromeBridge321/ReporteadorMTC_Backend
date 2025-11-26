<?php
namespace App\Http\Controllers;

use App\Http\Requests\PozosIdRequest;
use App\Services\DatabaseConnectionService;
use Illuminate\Support\Facades\DB;

class GenerarReporteMensual extends Controller
{
    private $dbConnectionService;

    public function __construct(DatabaseConnectionService $dbConnectionService)
    {
        $this->dbConnectionService = $dbConnectionService;
    }

    public function generarReporteMensual(PozosIdRequest $request)
    {
        $nombreConexion = $request->input('Conexion');

        // Validar que la conexión esté en la lista blanca
        if (! $nombreConexion || ! $this->dbConnectionService->esConexionValida($nombreConexion)) {
            return response()->json([
                'error'                  => 'Conexión no válida',
                'conexiones_disponibles' => $this->dbConnectionService->getConexionesDisponibles(),
            ], 400);
        }

        $conexion       = $this->dbConnectionService->obtenerConexion($nombreConexion);
        $pozosIDs       = (array) $request->input('Pozos', []);
        $mes            = $request->input('Fecha');
        $ReporteMensual = [];

        if (empty($pozosIDs) || empty($mes)) {
            return response()->json($ReporteMensual, 200);
        }

        $sql = $this->construirConsultaMensual($conexion, $mes);

        foreach ($pozosIDs as $pozoId) {
            $consulta = DB::connection($conexion)->select($sql, [$pozoId]);
            if (! empty($consulta)) {
                // Convertir los resultados a array de objetos simples
                $registros = array_map(function ($registro) {
                    return (array) $registro;
                }, $consulta);

                // Agregar al array en el nuevo formato
                $ReporteMensual[] = [
                    'nombrePozo' => $consulta[0]->Pozo,
                    'reporte'    => "Mensual",
                    'registros'  => $registros,
                ];
            }
        }

        return response()->json($ReporteMensual, 200);
    }

    private function construirConsultaMensual(string $dbName, string $mes): string
    {
        return "
    SET LANGUAGE Spanish;
    DECLARE @Mes VARCHAR(7) = '$mes';
    DECLARE @FechaInicio DATE = DATEFROMPARTS(YEAR(@Mes + '-01'), MONTH(@Mes + '-01'), 1);
    DECLARE @FechaFin DATE = EOMONTH(@FechaInicio);

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
            AVG(CASE WHEN PT.Nombre = 'TEMPERATURA_SUCCION' THEN VH.Valor END) AS TempSuccion
        FROM [$dbName].[dbo].[t_Historicos.ValoresTags] VH
        INNER JOIN [t_Instalacion.Pozos] IP ON IP.IdPozo = VH.IdPozo
        INNER JOIN [t_Proceso.Tags] PT ON PT.IdTag = VH.IdTag
        WHERE VH.IdPozo = ?
          AND CONVERT(DATE, VH.Fecha) BETWEEN @FechaInicio AND @FechaFin
        GROUP BY CONVERT(DATE, VH.Fecha), IP.NombrePozo
    )

    SELECT
        ISNULL(P.Pozo, 'Sin Datos') AS Pozo,
        D.Fecha,
        FORMAT(D.Fecha, 'dd/MM/yyyy') AS Fecha_Formato,
        DATENAME(WEEKDAY, D.Fecha) AS Dia_Semana,
            ROUND(ISNULL(P.PresionTP, 0), 1) AS [Presion_TP],
            ROUND(ISNULL(P.PresionTR, 0), 1) AS [Presion_TR],
            ROUND(ISNULL(P.LDD, 0), 1) AS [LDD],
            ROUND(ISNULL(P.TempPozo, 0), 1) AS [Temperatura_Pozo],
            ROUND(ISNULL(P.PresionSuccion, 0), 1) AS [Presion_Succion],
            ROUND(ISNULL(P.PresionEstDesc, 0), 1) AS [Presion_Descarga],
            ROUND(ISNULL(P.Velocidad, 0), 1) AS [Velocidad],
            ROUND(ISNULL(P.TempDesc, 0), 1) AS [Temp_Descarga],
            ROUND(ISNULL(P.TempSuccion, 0), 1) AS [Temp_Succion]
    FROM Dias D
    LEFT JOIN Promedios P ON D.Fecha = P.Fecha
    ORDER BY D.Fecha
    OPTION (MAXRECURSION 0);
        ";
    }
}
