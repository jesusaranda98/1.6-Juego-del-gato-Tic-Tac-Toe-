<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class AdminStoreUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->role === 'administrador';
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:120'],
            'email' => ['required', 'email', 'max:150', Rule::unique('users', 'email')],
            'role' => ['required', Rule::in(['administrador', 'jugador'])],
            'password' => ['required', 'string', 'min:8'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'El nombre es obligatorio.',
            'email.required' => 'El correo electrónico es obligatorio.',
            'email.email' => 'Ingresa un correo electrónico válido.',
            'email.unique' => 'Ese correo electrónico ya está registrado.',
            'role.required' => 'El rol es obligatorio.',
            'role.in' => 'El rol debe ser administrador o jugador.',
            'password.required' => 'La contraseña es obligatoria.',
            'password.min' => 'La contraseña debe tener al menos 8 caracteres.',
        ];
    }
}