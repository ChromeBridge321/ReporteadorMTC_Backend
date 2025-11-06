<?php
namespace App\Http\Controllers;

use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;

class PozoController extends Controller
{
    /**
     * Obtiene la lista de pozos desde la base de datos
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function obtenerPozos()
    {
        try {
            //Ejecutar la consulta SQL
            $pozos = DB::select('select IdPozo, NombrePozo from [t_Instalacion.Pozos] where ' .
                " NombrePozo not like '%OCUPADO%' " .
                " and NombrePozo not like '%disponible' " .
                " and NombrePozo != 'dis' " .
                " and NombrePozo != '´dis' " .
                " and NombrePozo != 'des' " .
                " and NombrePozo != 'disp' " .
                " and NombrePozo != 'p' " .
                " and NombrePozo != 'di' " .
                " and NombrePozo != 'dip' " .
                " and NombrePozo != 'd' " .
                " and NombrePozo != '' " .
                " order by NombrePozo desc");

            // Retornar respuesta JSON exitosa
            return response()->json($pozos, 200);

        } catch (\Exception $e) {
            // Manejar errores y retornar respuesta de error
            return response()->json([
                'success' => false,
                'data'    => [],
                'error'   => $e->getMessage(),
            ], 500);
        }
    }
}