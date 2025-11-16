<?php
namespace App\Services;

class DatabaseConnectionService
{
    private const CONEXIONES_PERMITIDAS = [
        'bd_MTC_PozaRica' => 'bd_MTC_PozaRica',
        'DB2'             => 'bd_MTC_PozaRica',
        'DB3'             => 'bd_MTC_PozaRica',
        'DB4'             => 'bd_MTC_PozaRica',
    ]; // nombre amigable => nombre de conexión en config/database.php debe ser igual al nombre de la
       // base de datos definida en el archivo .env a ser posible 

    public function esConexionValida(string $nombreConexion): bool
    {
        return isset(self::CONEXIONES_PERMITIDAS[$nombreConexion]);
    }

    public function obtenerConexion(string $nombreConexion): ?string
    {
        return self::CONEXIONES_PERMITIDAS[$nombreConexion] ?? null;
    }

    public function getConexionesDisponibles(): array
    {
        return array_keys(self::CONEXIONES_PERMITIDAS);
    }
}
