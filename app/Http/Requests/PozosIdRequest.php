<?php
namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class PozosIdRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'Pozos'    => 'sometimes|array|min:1',
            'Pozos.*'  => 'integer',
            'Fecha'    => 'sometimes|date',
            'Conexion' => 'sometimes|string',
        ];
    }
}
