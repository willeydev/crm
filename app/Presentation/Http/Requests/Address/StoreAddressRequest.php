<?php

namespace App\Presentation\Http\Requests\Address;

use Illuminate\Http\Request;

class StoreAddressRequest extends Request
{
    public function rules(): array
    {
        return [
            'cep'        => 'required|string|size:8',
            'number'     => 'required|string|max:20',
            'complement' => 'nullable|string|max:100',
        ];
    }

    public function messages(): array
    {
        return [
            'cep.required'    => 'O CEP é obrigatório.',
            'cep.size'        => 'O CEP deve ter exatamente 8 dígitos.',
            'number.required' => 'O número é obrigatório.',
            'number.max'      => 'O número deve ter no máximo 20 caracteres.',
            'complement.max'  => 'O complemento deve ter no máximo 100 caracteres.',
        ];
    }
}
