<?php

use Illuminate\Support\Facades\Route;

/**
 * ============================================================================
 * RUTAS DE LA API - SISTEMA REPORTEADOR DE POZOS
 * ============================================================================
 *
 * Este archivo define todos los endpoints disponibles en la API.
 * Todas las rutas están protegidas por el middleware CORS global.
 *
 * Base URL: http://dominio.com/api/
 */

/**
 * Ruta raíz - Verificación de que la API está funcionando
 *
 * GET /
 * Respuesta: "api works"
 */
Route::get('/', function () {
    return 'api works';
});

/**
 * Obtener lista de pozos disponibles
 *
 * GET /api/pozos
 * Parámetros: Conexion (string) - Nombre de la base de datos
 * Respuesta: Array de objetos con IdPozo y NombrePozo
 */
Route::get('api/pozos', 'PozoController@obtenerPozos');

/**
 * Generar reporte diario de pozos
 *
 * GET /api/pozos/reporte
 * Parámetros:
 *   - Conexion (string): Nombre de la base de datos
 *   - Pozos (array): Array de IDs de pozos
 *   - Fecha (date): Fecha del reporte en formato Y-m-d
 * Respuesta: Array de reportes con promedios horarios por pozo
 */
Route::get('api/pozos/reporte', 'GenerarReporteController@generarReporteConexion');

/**
 * Generar reporte mensual de pozos
 *
 * GET /api/pozos/reporte/mensual
 * Parámetros:
 *   - Conexion (string): Nombre de la base de datos
 *   - Pozos (array): Array de IDs de pozos
 *   - Fecha (date): Mes del reporte en formato Y-m
 * Respuesta: Array de reportes consolidados mensuales
 *
 * NOTA: Este endpoint requiere el controlador GenerarReporteMensual
 */
Route::get('api/pozos/reporte/mensual', 'GenerarReporteMensual@generarReporteMensual');
