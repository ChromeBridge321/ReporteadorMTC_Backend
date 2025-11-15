<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class GenerarReporteMensual extends Controller
{
    public function generarReporteMensual(Request $request)
    {
        $mes = $request->query('mes', '2025-11');  
        $pozoId = $request->query('idPozo', 168);

        $ReporteMensual = DB::select("
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
            CONVERT(DATE, VH.Fecha) AS Fecha,
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
          AND CONVERT(DATE, VH.Fecha) BETWEEN @FechaInicio AND @FechaFin
        GROUP BY CONVERT(DATE, VH.Fecha)
    )

    SELECT
        D.Fecha,
        FORMAT(D.Fecha, 'dd/MM/yyyy') AS Fecha_Formato,
        DATENAME(WEEKDAY, D.Fecha) AS Dia_Semana,
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
    FROM Dias D
    LEFT JOIN Promedios P ON D.Fecha = P.Fecha
    ORDER BY D.Fecha
    OPTION (MAXRECURSION 0);
    ");

        return response()->json($ReporteMensual, 200);
    }

    public function generarReporteMensualPorID(Request $request)
    {
        $mes = '2025-11';  
        $pozoId = $request->input('idPozo');

        $ReporteMensual = DB::select("
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
            CONVERT(DATE, VH.Fecha) AS Fecha,
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
          AND CONVERT(DATE, VH.Fecha) BETWEEN @FechaInicio AND @FechaFin
        GROUP BY CONVERT(DATE, VH.Fecha)
    )

    SELECT
        D.Fecha,
        FORMAT(D.Fecha, 'dd/MM/yyyy') AS Fecha_Formato,
        DATENAME(WEEKDAY, D.Fecha) AS Dia_Semana,
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
    FROM Dias D
    LEFT JOIN Promedios P ON D.Fecha = P.Fecha
    ORDER BY D.Fecha
    OPTION (MAXRECURSION 0);
    ");

        return response()->json($ReporteMensual, 200);
    }

    private function validateRequest(Request $request)
    {
        $request->validate([
            'idPozo' => 'required|integer',
            'mes' => 'required|date_format:Y-m',
        ]);
    }
}
