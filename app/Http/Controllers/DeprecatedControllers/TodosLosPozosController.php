<?php
namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;

class TodosLosPozosController extends Controller
{
    public function obtenerTodosLosPozos()
    {
        try {
            $pozos = DB::select('select * from [t_Instalacion.Pozos] order by NombrePozo desc');
            return response()->json($pozos, 200);
        } catch (\Throwable $th) {
            return response()->json([
                'success' => false,
                'data'    => [],
                'error'   => $th->getMessage(),
            ], 500);
        }
    }
}