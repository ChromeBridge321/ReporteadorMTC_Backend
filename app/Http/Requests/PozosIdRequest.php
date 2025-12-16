<?php
namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Request de validación para operaciones con pozos
 *
 * Valida los parámetros comunes utilizados en los endpoints de pozos:
 * - Pozos: Array de IDs de pozos (opcional)
 * - Fecha: Fecha del reporte (opcional)
 * - Conexion: Nombre de la base de datos a consultar (opcional)
 */
class PozosIdRequest extends FormRequest
{
    /**
     * Determina si el usuario está autorizado para hacer esta petición
     *
     * Por defecto retorna true, permitiendo todas las peticiones.
     * Se puede modificar para agregar lógica de autorización específica.
     *
     * @return bool Siempre true (sin autenticación por ahora)
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Reglas de validación para la petición
     *
     * Validaciones aplicadas:
     * - Pozos: Array opcional, mínimo 1 elemento, cada elemento debe ser entero
     * - Fecha: String opcional en formato de fecha válido (Y-m-d)
     * - Conexion: String opcional con el nombre de la conexión BD
     *
     * @return array Reglas de validación Laravel
     */
    public function rules()
    {
        return [
            'Pozos'    => 'sometimes|array|min:1', // Array opcional con al menos 1 elemento
            'Pozos.*'  => 'integer',               // Cada elemento debe ser un entero (ID de pozo)
            'Fecha'    => 'sometimes|date',        // Fecha opcional en formato válido
            'Conexion' => 'sometimes|string',      // Nombre de conexión opcional
        ];
    }
}
