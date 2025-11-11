<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class GenerarReporteController extends Controller
{
    public function generarReporte(Request $request)
    {
        $fecha  = '2025-11-10';
        $pozoId = 168;

        $ReportePozo = DB::select("
    DECLARE @Dia DATE = '$fecha';

    WITH Horas AS (
        SELECT 0 AS Hora
        UNION ALL
        SELECT Hora + 1 FROM Horas WHERE Hora < 23
    ),
    Promedios AS (
        SELECT
            DATEPART(HOUR, VH.Fecha) AS Hora,
            AVG(CASE WHEN PT.Nombre = 'PRESION_TP' THEN VH.Valor END) AS PresionTP,
            AVG(CASE WHEN PT.Nombre = 'PRESION_TR' THEN VH.Valor END) AS PresionTR,
            AVG(CASE WHEN PT.Nombre = '?' THEN VH.Valor END) AS LDD,
            AVG(CASE WHEN PT.Nombre = '?' THEN VH.Valor END) AS TempPozo,
            AVG(CASE WHEN PT.Nombre = 'TEMPERATURA_SUCCION' THEN VH.Valor END) AS TempLE,
            AVG(CASE WHEN PT.Nombre = 'TEMPERATURA_DESCARGA' THEN VH.Valor END) AS TempDesc,
            AVG(CASE WHEN PT.Nombre = 'PRESION_SUCCION' THEN VH.Valor END) AS PresionSuccion,
            AVG(CASE WHEN PT.Nombre = 'PRESION_ESTATICA_DESCARGA' THEN VH.Valor END) AS PresionEstDesc,
            AVG(CASE WHEN PT.Nombre = 'VELOCIDAD' THEN VH.Valor END) AS Velocidad,
            AVG(CASE WHEN PT.Nombre = '?' THEN VH.Valor END) AS TempDescarga,
            AVG(CASE WHEN PT.Nombre = '?' THEN VH.Valor END) AS TempSuccion
        FROM [bd_MTC_PozaRica].[dbo].[t_Historicos.ValoresTags] VH
        INNER JOIN [t_Instalacion.Pozos] IP ON IP.IdPozo = VH.IdPozo
        INNER JOIN [t_Proceso.Tags] PT ON PT.IdTag = VH.IdTag
        WHERE VH.IdPozo = $pozoId
          AND CONVERT(date, VH.Fecha) = @Dia
        GROUP BY DATEPART(HOUR, VH.Fecha)
    )

    SELECT
        H.Hora,
        FORMAT(H.Hora, '00') + ':00' AS Hora_Formato,
        ROUND(ISNULL(P.PresionTP, 0), 1) AS [Presión TP],
        ROUND(ISNULL(P.PresionTR, 0), 1) AS [Presión TR],
        ROUND(ISNULL(P.LDD, 0), 1) AS [LDD],
        ROUND(ISNULL(P.TempPozo, 0), 1) AS [Temperatura Pozo],
        ROUND(ISNULL(P.TempLE, 0), 1) AS [Temp LE],
        ROUND(ISNULL(P.TempDesc, 0), 1) AS [Temp. Descarga],
        ROUND(ISNULL(P.PresionSuccion, 0), 1) AS [Presión Succión],
        ROUND(ISNULL(P.PresionEstDesc, 0), 1) AS [Presión Estática Descarga],
        ROUND(ISNULL(P.Velocidad, 0), 1) AS [Velocidad],
        ROUND(ISNULL(P.TempDescarga, 0), 1) AS [Temperatura Descarga],
        ROUND(ISNULL(P.TempSuccion, 0), 1) AS [Temperatura Succión]
    FROM Horas H
    LEFT JOIN Promedios P ON H.Hora = P.Hora
    ORDER BY H.Hora
    OPTION (MAXRECURSION 0);
    ");

        return response()->json($ReportePozo, 200);
    }

    private function validateRequest(Request $request)
    {
        $request->validate([
            'idPozo' => 'required|integer',
        ]);
    }

    public function reportePozoPorID(array $pozos)
    {
        foreach ($pozos as $idPozo) {
            $this->generarReportePorID($idPozo);
        }
    }

    public function generarReportePorID(Request $request)
    {
        $fecha  = '2025-11-10';
        $pozoId = $request->input('idPozo');

        $ReportePozo = DB::select("
    DECLARE @Dia DATE = '$fecha';

    WITH Horas AS (
        SELECT 0 AS Hora
        UNION ALL
        SELECT Hora + 1 FROM Horas WHERE Hora < 23
    ),
    Promedios AS (
        SELECT
            DATEPART(HOUR, VH.Fecha) AS Hora,
            AVG(CASE WHEN PT.Nombre = 'PRESION_TP' THEN VH.Valor END) AS PresionTP,
            AVG(CASE WHEN PT.Nombre = 'PRESION_TR' THEN VH.Valor END) AS PresionTR,
            AVG(CASE WHEN PT.Nombre = '?' THEN VH.Valor END) AS LDD,
            AVG(CASE WHEN PT.Nombre = '?' THEN VH.Valor END) AS TempPozo,
            AVG(CASE WHEN PT.Nombre = 'TEMPERATURA_SUCCION' THEN VH.Valor END) AS TempLE,
            AVG(CASE WHEN PT.Nombre = 'TEMPERATURA_DESCARGA' THEN VH.Valor END) AS TempDesc,
            AVG(CASE WHEN PT.Nombre = 'PRESION_SUCCION' THEN VH.Valor END) AS PresionSuccion,
            AVG(CASE WHEN PT.Nombre = 'PRESION_ESTATICA_DESCARGA' THEN VH.Valor END) AS PresionEstDesc,
            AVG(CASE WHEN PT.Nombre = 'VELOCIDAD' THEN VH.Valor END) AS Velocidad,
            AVG(CASE WHEN PT.Nombre = '?' THEN VH.Valor END) AS TempDescarga,
            AVG(CASE WHEN PT.Nombre = '?' THEN VH.Valor END) AS TempSuccion
        FROM [bd_MTC_PozaRica].[dbo].[t_Historicos.ValoresTags] VH
        INNER JOIN [t_Instalacion.Pozos] IP ON IP.IdPozo = VH.IdPozo
        INNER JOIN [t_Proceso.Tags] PT ON PT.IdTag = VH.IdTag
        WHERE VH.IdPozo = $pozoId
          AND CONVERT(date, VH.Fecha) = @Dia
        GROUP BY DATEPART(HOUR, VH.Fecha)
    )

    SELECT
        H.Hora,
        FORMAT(H.Hora, '00') + ':00' AS Hora_Formato,
        ROUND(ISNULL(P.PresionTP, 0), 1) AS [Presión TP],
        ROUND(ISNULL(P.PresionTR, 0), 1) AS [Presión TR],
        ROUND(ISNULL(P.LDD, 0), 1) AS [LDD],
        ROUND(ISNULL(P.TempPozo, 0), 1) AS [Temperatura Pozo],
        ROUND(ISNULL(P.TempLE, 0), 1) AS [Temp LE],
        ROUND(ISNULL(P.TempDesc, 0), 1) AS [Temp. Descarga],
        ROUND(ISNULL(P.PresionSuccion, 0), 1) AS [Presión Succión],
        ROUND(ISNULL(P.PresionEstDesc, 0), 1) AS [Presión Estática Descarga],
        ROUND(ISNULL(P.Velocidad, 0), 1) AS [Velocidad],
        ROUND(ISNULL(P.TempDescarga, 0), 1) AS [Temperatura Descarga],
        ROUND(ISNULL(P.TempSuccion, 0), 1) AS [Temperatura Succión]
    FROM Horas H
    LEFT JOIN Promedios P ON H.Hora = P.Hora
    ORDER BY H.Hora
    OPTION (MAXRECURSION 0);
    ");

        return response()->json($ReportePozo, 200);
    }

}
