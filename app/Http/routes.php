<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for an application.
| It's a breeze. Simply tell Laravel the URIs it should respond to
| and give it the controller to call when that URI is requested.
|
*/
/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/
// Ruta para obtener pozos
Route::get('api/pozos', 'PozoController@obtenerPozos');
Route::get('api/pozos/reporte', 'GenerarReporteController@generarReporteConexion');
Route::get('api/pozos/reporte/mensual', 'GenerarReporteMensual@generarReporteMensual');
