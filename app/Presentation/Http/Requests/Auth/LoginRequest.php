<?php

namespace App\Presentation\Http\Requests\Auth;

use Illuminate\Http\Request;

class LoginRequest extends Request
{
    public function rules(): array
    {
        return [
            'email'    => 'required|email',
            'password' => 'required|string',
        ];
    }

    public function messages(): array
    {
        return [
            'email.required'    => 'O e-mail é obrigatório.',
            'email.email'       => 'Informe um e-mail válido.',
            'password.required' => 'A senha é obrigatória.',
            'password.string'   => 'A senha deve ser um texto.',
        ];
    }
}
