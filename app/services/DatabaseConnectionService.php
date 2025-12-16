<?php
namespace App\Services;

/**
 * Servicio de gestión de conexiones a bases de datos
 *
 * Centraliza el manejo de múltiples conexiones a bases de datos SQL Server.
 * Actúa como whitelist de conexiones permitidas para mayor seguridad.
 *
 * CONFIGURACIÓN:
 * 1. Agregar la conexión aquí en CONEXIONES_PERMITIDAS
 * 2. Configurar credenciales en .env con el formato:
 *    DB_CONNECTION_NOMBRE=sqlsrv
 *    DB_HOST_NOMBRE=servidor
 *    DB_DATABASE_NOMBRE=nombre_bd
 *    DB_USERNAME_NOMBRE=usuario
 *    DB_PASSWORD_NOMBRE=contraseña
 * 3. Registrar en config/database.php bajo 'connections'
 */
class DatabaseConnectionService
{
    /**
     * Mapeo de conexiones permitidas en el sistema
     *
     * Formato: 'nombre_amigable' => 'nombre_conexion_laravel'
     *
     * El nombre de la conexión debe coincidir con:
     * - La clave en config/database.php ['connections']
     * - El nombre usado en las variables de entorno .env
     *
     * @var array
     */
    private const CONEXIONES_PERMITIDAS = [
        'bd_MTC_PozaRica'  => 'bd_MTC_PozaRica',  // Base de datos MTC Poza Rica
        'bd_SDMC_Motocomp' => 'bd_SDMC_Motocomp', // Base de datos SDMC Motocomp
        'bd_MTC_Muspac'    => 'bd_MTC_Muspac',    // Base de datos MTC Muspac
        'bd_Bellota'       => 'bd_Bellota',       // Base de datos Bellota
        'bd_MTC_CincoP'    => 'bd_MTC_CincoP',    // Base de datos MTC Cinco Presidentes
    ];

    /**
     * Verifica si una conexión está permitida en el sistema
     *
     * @param string $nombreConexion Nombre de la conexión a validar
     * @return bool true si la conexión está en la whitelist, false en caso contrario
     */
    public function esConexionValida(string $nombreConexion): bool
    {
        return isset(self::CONEXIONES_PERMITIDAS[$nombreConexion]);
    }

    /**
     * Obtiene el nombre real de la conexión Laravel
     *
     * @param string $nombreConexion Nombre amigable de la conexión
     * @return string|null Nombre de la conexión Laravel o null si no existe
     */
    public function obtenerConexion(string $nombreConexion): ?string
    {
        return self::CONEXIONES_PERMITIDAS[$nombreConexion] ?? null;
    }

    /**
     * Obtiene todas las conexiones disponibles en el sistema
     *
     * Útil para mostrar al usuario las opciones de bases de datos disponibles.
     *
     * @return array Array con los nombres amigables de las conexiones
     */
    public function getConexionesDisponibles(): array
    {
        return array_keys(self::CONEXIONES_PERMITIDAS);
    }
}
