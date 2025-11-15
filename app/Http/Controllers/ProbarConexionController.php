<?php
namespace App\Http\Controllers;

use App\Http\Requests\Request;
use App\Services\DatabaseConnectionService;

class ProbarConexionController extends Controller
{

    private $dbConnectionService;

    public function __construct(DatabaseConnectionService $dbConnectionService)
    {
        $this->dbConnectionService = $dbConnectionService;
    }

    public function probarConexion(Request $request)
    {
        $conexiones = $this->dbConnectionService->getConexionesDisponibles();
        return response()->json(['conexiones_disponibles' => $conexiones]);
    }
}
