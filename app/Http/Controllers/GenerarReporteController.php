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

    public function generarReporteConexion(PozosIdRequest $request)
    {
        $nombreConexion = $request->input('Conexion');

        // Validar que la conexión esté en la lista blanca
        if (! $nombreConexion || ! $this->dbConnectionService->esConexionValida($nombreConexion)) {
            return response()->json([
                'error'                  => 'Conexión no válida',
                'conexiones_disponibles' => $this->dbConnectionService->getConexionesDisponibles(),
            ], 400);
        }

        $conexion    = $this->dbConnectionService->obtenerConexion($nombreConexion);
        $pozosIDs    = (array) $request->input('Pozos', []);
        $fecha       = $request->input('Fecha');
        $ReportePozo = [];

        if (empty($pozosIDs) || empty($fecha)) {
            return response()->json($ReportePozo, 200);
        }

        $sql = $this->construirConsulta($conexion);

        foreach ($pozosIDs as $pozoId) {
            $consulta = DB::connection($conexion)->select($sql, [$pozoId, $fecha]);
            if (! empty($consulta)) {
                // Convertir los resultados a array de objetos simples
                $registros = array_map(function ($registro) {
                    return (array) $registro;
                }, $consulta);

                // Agregar al array en el nuevo formato
                $ReportePozo[] = [
                    'nombrePozo' => $consulta[0]->Pozo,
                    'reporte'    => "Diario",
                    'registros'  => $registros,
                ];
            }
        }

        return response()->json($ReportePozo, 200);
    }

    private function construirConsulta(string $dbName): string
    {
        return "
        WITH Horas AS (
            SELECT 0 AS Hora
            UNION ALL
            SELECT Hora + 1 FROM Horas WHERE Hora < 23
        ),
        Promedios AS (
            SELECT
                IP.NombrePozo AS Pozo,
                DATEPART(HOUR, VH.Fecha) AS Hora,
                AVG(CASE WHEN PT.Nombre = 'PRESION_TP' THEN VH.Valor END) AS PresionTP,
                AVG(CASE WHEN PT.Nombre = 'PRESION_TR' THEN VH.Valor END) AS PresionTR,
                AVG(CASE WHEN PT.Nombre = 'TAG_LDD' THEN VH.Valor END) AS LDD,
                AVG(CASE WHEN PT.Nombre = 'TAG_TEMPPozo' THEN VH.Valor END) AS TempPozo,
                AVG(CASE WHEN PT.Nombre = 'TEMPERATURA_SUCCION' THEN VH.Valor END) AS TempLE,
                AVG(CASE WHEN PT.Nombre = 'TEMPERATURA_DESCARGA' THEN VH.Valor END) AS TempDesc,
                AVG(CASE WHEN PT.Nombre = 'PRESION_SUCCION' THEN VH.Valor END) AS PresionSuccion,
                AVG(CASE WHEN PT.Nombre = 'PRESION_ESTATICA_DESCARGA' THEN VH.Valor END) AS PresionEstDesc,
                AVG(CASE WHEN PT.Nombre = 'VELOCIDAD' THEN VH.Valor END) AS Velocidad,
                AVG(CASE WHEN PT.Nombre = 'TAG_TempDescarga' THEN VH.Valor END) AS TempDescarga,
                AVG(CASE WHEN PT.Nombre = 'TAG_TempSuccion' THEN VH.Valor END) AS TempSuccion
            FROM [$dbName].[dbo].[t_Historicos.ValoresTags] VH
            INNER JOIN [t_Instalacion.Pozos] IP ON IP.IdPozo = VH.IdPozo
            INNER JOIN [t_Proceso.Tags] PT ON PT.IdTag = VH.IdTag
            WHERE VH.IdPozo = ?
              AND CONVERT(date, VH.Fecha) = ?
            GROUP BY DATEPART(HOUR, VH.Fecha), IP.NombrePozo
        )
        SELECT
            ISNULL(P.Pozo, 'Sin Datos') AS Pozo,
            H.Hora,
            FORMAT(H.Hora, '00') + ':00' AS Hora_Formato,
            ROUND(ISNULL(P.PresionTP, 0), 1) AS [Presion_TP],
            ROUND(ISNULL(P.PresionTR, 0), 1) AS [Presion_TR],
            ROUND(ISNULL(P.LDD, 0), 1) AS [LDD],
            ROUND(ISNULL(P.TempPozo, 0), 1) AS [Temperatura_Pozo],
            ROUND(ISNULL(P.TempLE, 0), 1) AS [Temp_LE],
            ROUND(ISNULL(P.TempDesc, 0), 1) AS [Temp_Descarga],
            ROUND(ISNULL(P.PresionSuccion, 0), 1) AS [Presion_Succion],
            ROUND(ISNULL(P.PresionEstDesc, 0), 1) AS [Presion_Estatica_Descarga],
            ROUND(ISNULL(P.Velocidad, 0), 1) AS [Velocidad],
            ROUND(ISNULL(P.TempDescarga, 0), 1) AS [Temperatura_Descarga],
            ROUND(ISNULL(P.TempSuccion, 0), 1) AS [Temperatura_Succion]
        FROM Horas H
        LEFT JOIN Promedios P ON H.Hora = P.Hora
        ORDER BY H.Hora
        OPTION (MAXRECURSION 0);
        ";
    }
}
