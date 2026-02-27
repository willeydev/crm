<?php

namespace App\Presentation\Http\Requests\Address;

use Illuminate\Http\Request;

class UpdateAddressRequest extends Request
{
    public function rules(): array
    {
        return [
            'cep'        => 'sometimes|string|size:8',
            'number'     => 'sometimes|string|max:20',
            'complement' => 'nullable|string|max:100',
        ];
    }

    public function messages(): array
    {
        return [
            'cep.size'       => 'O CEP deve ter exatamente 8 dígitos.',
            'number.max'     => 'O número deve ter no máximo 20 caracteres.',
            'complement.max' => 'O complemento deve ter no máximo 100 caracteres.',
        ];
    }
}
