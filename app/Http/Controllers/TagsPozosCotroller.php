<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TagsPozosCotroller extends Controller
{
    public function obtenerTagsPozos(Request $request)
    {
        try {
            $idPozo = $request->query('idPozo');

            $tagsPozos = DB::select('SELECT TagId, TagName FROM TagsPozos WHERE IdPozo = ?', [$idPozo]);

            return response()->json($tagsPozos, 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'data'    => [],
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

}