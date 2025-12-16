<?php

/**
 * ============================================================================
 * CONFIGURACIÓN DE BASES DE DATOS - SISTEMA REPORTEADOR DE POZOS
 * ============================================================================
 *
 * Este archivo configura todas las conexiones a bases de datos disponibles.
 * El sistema soporta múltiples conexiones SQL Server simultáneas para
 * consultar datos de diferentes instalaciones de pozos.
 */

return [

    /**
     * Estilo de recuperación de datos PDO
     *
     * PDO::FETCH_CLASS retorna resultados como objetos stdClass
     * Alternativas: PDO::FETCH_ASSOC (arrays asociativos), PDO::FETCH_OBJ (objetos)
     */
    'fetch'       => PDO::FETCH_CLASS,

    /**
     * Conexión de base de datos por defecto
     *
     * Especifica qué conexión usar cuando no se especifica una explícitamente.
     * Por defecto usa SQL Server (sqlsrv).
     */
    'default'     => env('DB_CONNECTION', 'sqlsrv'),

    /**
     * ========================================================================
     * CONEXIONES DE BASES DE DATOS
     * ========================================================================
     *
     * Define todas las conexiones disponibles en el sistema.
     * Cada conexión se configura mediante variables de entorno (.env)
     *
     * TIPOS SOPORTADOS:
     * - SQLite: Base de datos ligera en archivo
     * - MySQL: Base de datos relacional popular
     * - PostgreSQL: Base de datos avanzada de código abierto
     * - SQL Server: Base de datos empresarial de Microsoft (USADA EN ESTE PROYECTO)
     */
    'connections' => [

        /**
         * Conexión SQLite (No usada en este proyecto)
         * Base de datos ligera en archivo, útil para desarrollo y testing
         */
        'sqlite'           => [
            'driver'   => 'sqlite',
            'database' => env('DB_DATABASE', database_path('database.sqlite')),
            'prefix'   => '',
        ],

        /**
         * Conexión MySQL (No usada en este proyecto)
         * Base de datos relacional popular, configuración de ejemplo
         */
        'mysql'            => [
            'driver'    => 'mysql',
            'host'      => env('DB_HOST', 'localhost'),
            'port'      => env('DB_PORT', '3306'),
            'database'  => env('DB_DATABASE', 'forge'),
            'username'  => env('DB_USERNAME', 'forge'),
            'password'  => env('DB_PASSWORD', ''),
            'charset'   => 'utf8',
            'collation' => 'utf8_unicode_ci',
            'prefix'    => '',
            'strict'    => false,
            'engine'    => null,
        ],

        /**
         * Conexión PostgreSQL (No usada en este proyecto)
         * Base de datos avanzada, configuración de ejemplo
         */
        'pgsql'            => [
            'driver'   => 'pgsql',
            'host'     => env('DB_HOST', 'localhost'),
            'port'     => env('DB_PORT', '5432'),
            'database' => env('DB_DATABASE', 'forge'),
            'username' => env('DB_USERNAME', 'forge'),
            'password' => env('DB_PASSWORD', ''),
            'charset'  => 'utf8',
            'prefix'   => '',
            'schema'   => 'public',
        ],

        /**
         * Conexión SQL Server genérica (No usada - Se usan conexiones específicas)
         * Configuración de ejemplo para SQL Server
         */
        'sqlsrv'           => [
            'driver'   => 'sqlsrv',
            'host'     => env('DB_HOST', 'localhost'),
            'port'     => env('DB_PORT', '1433'),
            'database' => env('DB_DATABASE', ''),
            'username' => env('DB_USERNAME', ''),
            'password' => env('DB_PASSWORD', ''),
            'charset'  => 'utf8',
            'prefix'   => '',
        ],

        /**
         * ====================================================================
         * CONEXIONES ESPECÍFICAS DE POZOS (SQL Server)
         * ====================================================================
         *
         * Estas son las conexiones activas utilizadas por el sistema.
         * Cada conexión representa una instalación de pozos diferente.
         *
         * IMPORTANTE:
         * - Los nombres deben coincidir con DatabaseConnectionService::CONEXIONES_PERMITIDAS
         * - Las variables de entorno se definen en .env con formato: DB1_*, DB2_*, etc.
         * - Cada conexión debe tener su propio conjunto de credenciales
         */

        /**
         * Conexión: bd_MTC_PozaRica
         * Base de datos de la instalación MTC Poza Rica
         * Variables: DB1_HOST, DB1_PORT, DB1_DATABASE, DB1_USERNAME, DB1_PASSWORD
         */
        'bd_MTC_PozaRica'  => [
            'driver'   => 'sqlsrv',
            'host'     => env('DB1_HOST', 'localhost'),
            'port'     => env('DB1_PORT', '1433'),
            'database' => env('DB1_DATABASE', ''),
            'username' => env('DB1_USERNAME', ''),
            'password' => env('DB1_PASSWORD', ''),
            'charset'  => 'utf8',
            'prefix'   => '',
        ],

        /**
         * Conexión: bd_SDMC_Motocomp
         * Base de datos de la instalación SDMC Motocomp
         * Variables: DB2_HOST, DB2_PORT, DB2_DATABASE, DB2_USERNAME, DB2_PASSWORD
         */
        'bd_SDMC_Motocomp' => [
            'driver'   => 'sqlsrv',
            'host'     => env('DB2_HOST', 'localhost'),
            'port'     => env('DB2_PORT', '1433'),
            'database' => env('DB2_DATABASE', ''),
            'username' => env('DB2_USERNAME', ''),
            'password' => env('DB2_PASSWORD', ''),
            'charset'  => 'utf8',
            'prefix'   => '',
        ],

        /**
         * Conexión: bd_MTC_Muspac
         * Base de datos de la instalación MTC Muspac
         * Variables: DB3_HOST, DB3_PORT, DB3_DATABASE, DB3_USERNAME, DB3_PASSWORD
         */
        'bd_MTC_Muspac'    => [
            'driver'   => 'sqlsrv',
            'host'     => env('DB3_HOST', 'localhost'),
            'port'     => env('DB3_PORT', '1433'),
            'database' => env('DB3_DATABASE', ''),
            'username' => env('DB3_USERNAME', ''),
            'password' => env('DB3_PASSWORD', ''),
            'charset'  => 'utf8',
            'prefix'   => '',
        ],

        /**
         * Conexión: bd_Bellota
         * Base de datos de la instalación Bellota
         * Variables: DB4_HOST, DB4_PORT, DB4_DATABASE, DB4_USERNAME, DB4_PASSWORD
         */
        'bd_Bellota'       => [
            'driver'   => 'sqlsrv',
            'host'     => env('DB4_HOST', 'localhost'),
            'port'     => env('DB4_PORT', '1433'),
            'database' => env('DB4_DATABASE', ''),
            'username' => env('DB4_USERNAME', ''),
            'password' => env('DB4_PASSWORD', ''),
            'charset'  => 'utf8',
            'prefix'   => '',
        ],

        /**
         * Conexión: bd_MTC_CincoP
         * Base de datos de la instalación MTC Cinco Presidentes
         * Variables: DB5_HOST, DB5_PORT, DB5_DATABASE, DB5_USERNAME, DB5_PASSWORD
         */
        'bd_MTC_CincoP'    => [
            'driver'   => 'sqlsrv',
            'host'     => env('DB5_HOST', 'localhost'),
            'port'     => env('DB5_PORT', '1433'),
            'database' => env('DB5_DATABASE', ''),
            'username' => env('DB5_USERNAME', ''),
            'password' => env('DB5_PASSWORD', ''),
            'charset'  => 'utf8',
            'prefix'   => '',
        ],

    ],

    /**
     * ========================================================================
     * TABLA DE REPOSITORIO DE MIGRACIONES
     * ========================================================================
     *
     * Nombre de la tabla que almacena el historial de migraciones ejecutadas.
     * Laravel usa esta tabla para rastrear qué migraciones se han aplicado
     * y cuáles están pendientes.
     */
    'migrations'  => 'migrations',

    /**
     * ========================================================================
     * CONFIGURACIÓN DE REDIS
     * ========================================================================
     *
     * Redis es un almacén de datos clave-valor en memoria de alto rendimiento.
     * Útil para caché, sesiones, colas y pub/sub.
     *
     * NOTA: No utilizado actualmente en este proyecto, pero disponible
     * para futuras implementaciones de caché o manejo de sesiones.
     */
    'redis'       => [

        /**
         * Modo cluster deshabilitado
         * Si se necesita Redis Cluster para alta disponibilidad, cambiar a true
         */
        'cluster' => false,

        /**
         * Conexión Redis por defecto
         * Configuración mediante variables de entorno en .env
         */
        'default' => [
            'host'     => env('REDIS_HOST', 'localhost'),
            'password' => env('REDIS_PASSWORD', null),
            'port'     => env('REDIS_PORT', 6379),
            'database' => 0, // Base de datos Redis (0-15 disponibles)
        ],

    ],

];
