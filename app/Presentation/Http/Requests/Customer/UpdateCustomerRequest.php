<?php

namespace App\Presentation\Http\Requests\Customer;

use Illuminate\Http\Request;

class UpdateCustomerRequest extends Request
{
    public function rules(): array
    {
        return [
            'name'     => 'sometimes|string|max:255',
            'email'    => 'sometimes|nullable|email|max:255',
            'phone'    => 'sometimes|nullable|string|max:20',
            'document' => 'sometimes|nullable|string|max:20',
        ];
    }

    public function messages(): array
    {
        return [
            'name.string'     => 'O nome deve ser um texto.',
            'name.max'        => 'O nome deve ter no máximo 255 caracteres.',
            'email.email'     => 'Informe um e-mail válido.',
            'email.max'       => 'O e-mail deve ter no máximo 255 caracteres.',
            'phone.string'    => 'O telefone deve ser um texto.',
            'phone.max'       => 'O telefone deve ter no máximo 20 caracteres.',
            'document.string' => 'O documento deve ser um texto.',
            'document.max'    => 'O documento deve ter no máximo 20 caracteres.',
        ];
    }
}
