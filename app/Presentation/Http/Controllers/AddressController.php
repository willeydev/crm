<?php

namespace App\Presentation\Http\Controllers;

use App\Application\Services\AddressService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class AddressController extends Controller
{
    public function __construct(private readonly AddressService $addressService) {}

    public function index(Request $request, int $customerId): JsonResponse
    {
        $addresses = $this->addressService->listByCustomer($customerId, $request->user()->id);
        return $this->success($addresses, 'Enderecos listados com sucesso.');
    }

    public function store(Request $request, int $customerId): JsonResponse
    {
        try {
            $this->validate($request, [
                'cep'        => ['required', 'string', 'size:8'],
                'number'     => ['required', 'string', 'max:20'],
                'complement' => ['nullable', 'string', 'max:100'],
            ], [
                'cep.required'    => 'O CEP e obrigatorio.',
                'cep.size'        => 'O CEP deve ter exatamente 8 digitos.',
                'number.required' => 'O numero e obrigatorio.',
                'number.max'      => 'O numero deve ter no maximo 20 caracteres.',
                'complement.max'  => 'O complemento deve ter no maximo 100 caracteres.',
            ]);
        } catch (ValidationException $e) {
            return $this->error('Dados invalidos.', 422, $e->errors());
        }

        $address = $this->addressService->create(
            $request->only('cep', 'number', 'complement'),
            $customerId,
            $request->user()->id
        );

        return $this->created($address, 'Endereco criado com sucesso.');
    }

    public function show(Request $request, int $customerId, int $id): JsonResponse
    {
        $address = $this->addressService->findOwned($id, $customerId, $request->user()->id);
        return $this->success($address, 'Endereco encontrado.');
    }

    public function update(Request $request, int $customerId, int $id): JsonResponse
    {
        try {
            $this->validate($request, [
                'cep'        => ['sometimes', 'string', 'size:8'],
                'number'     => ['sometimes', 'string', 'max:20'],
                'complement' => ['nullable', 'string', 'max:100'],
            ], [
                'cep.size'       => 'O CEP deve ter exatamente 8 digitos.',
                'number.max'     => 'O numero deve ter no maximo 20 caracteres.',
                'complement.max' => 'O complemento deve ter no maximo 100 caracteres.',
            ]);
        } catch (ValidationException $e) {
            return $this->error('Dados invalidos.', 422, $e->errors());
        }

        $address = $this->addressService->update(
            $id,
            $request->only('cep', 'number', 'complement'),
            $customerId,
            $request->user()->id
        );

        return $this->success($address, 'Endereco atualizado com sucesso.');
    }

    public function destroy(Request $request, int $customerId, int $id): JsonResponse
    {
        $this->addressService->delete($id, $customerId, $request->user()->id);
        return $this->noContent();
    }
}
