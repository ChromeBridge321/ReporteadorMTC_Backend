<?php
namespace App\Services;

class DatabaseConnectionService
{
    private const CONEXIONES_PERMITIDAS = [
        'poza_rica' => 'sqlsrv',
        'otra_bd'   => 'bd_MTC_Otra',
    ];

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
