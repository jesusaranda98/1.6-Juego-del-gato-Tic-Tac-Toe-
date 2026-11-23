<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class MakeMoveRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'cell' => ['required', 'integer', 'between:0,8'],
        ];
    }

    public function messages(): array
    {
        return [
            'cell.required' => 'Debes seleccionar una casilla del tablero.',
            'cell.integer' => 'La casilla seleccionada no es válida.',
            'cell.between' => 'La casilla debe estar entre 0 y 8.',
        ];
    }
}