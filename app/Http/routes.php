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

// Ruta de prueba
Route::get('api/test', function () {
    return response()->json(['status' => 'API is working!', 'timestamp' => date('Y-m-d H:i:s')]);
});

// Ruta para obtener pozos
Route::get('api/pozos', 'PozoController@obtenerPozos');
Route::get('api/pozos/todos', 'TodosLosPozosController@obtenerTodosLosPozos');

// Rutas para reportes
Route::get('api/pozos/reporte', 'GenerarReporteController@generarReporte');
Route::get('api/pozos/reporte/mensual', 'GenerarReporteMensual@generarReporteMensual');