<?php

namespace App\Presentation\Http\Requests\Auth;

use Illuminate\Http\Request;

class RegisterRequest extends Request
{
    public function rules(): array
    {
        return [
            'name'     => 'required|string|max:255',
            'email'    => 'required|email',
            'password' => 'required|string|min:8|confirmed',
        ];
    }

    public function messages(): array
    {
        return [
            'name.required'      => 'O nome é obrigatório.',
            'name.string'        => 'O nome deve ser um texto.',
            'name.max'           => 'O nome deve ter no máximo 255 caracteres.',
            'email.required'     => 'O e-mail é obrigatório.',
            'email.email'        => 'Informe um e-mail válido.',
            'password.required'  => 'A senha é obrigatória.',
            'password.string'    => 'A senha deve ser um texto.',
            'password.min'       => 'A senha deve ter no mínimo 8 caracteres.',
            'password.confirmed' => 'A confirmação de senha não confere.',
        ];
    }
}
