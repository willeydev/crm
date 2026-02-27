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
        return $this->success($addresses, 'Endereços listados com sucesso.');
    }

    public function store(Request $request, int $customerId): JsonResponse
    {
        try {
            $this->validate($request, [
                'cep'        => 'required|string|size:8',
                'number'     => 'required|string|max:20',
                'complement' => 'nullable|string|max:255',
            ], [
                'cep.required'    => 'O CEP é obrigatório.',
                'cep.string'      => 'O CEP deve ser um texto.',
                'cep.size'        => 'O CEP deve ter exatamente 8 caracteres.',
                'number.required' => 'O número é obrigatório.',
                'number.string'   => 'O número deve ser um texto.',
                'number.max'      => 'O número deve ter no máximo 20 caracteres.',
                'complement.string' => 'O complemento deve ser um texto.',
                'complement.max'    => 'O complemento deve ter no máximo 255 caracteres.',
            ]);
        } catch (ValidationException $e) {
            return $this->error('Dados inválidos.', 422, $e->errors());
        }

        $address = $this->addressService->create(
            $request->only('cep', 'number', 'complement'),
            $customerId,
            $request->user()->id
        );

        return $this->created($address, 'Endereço criado com sucesso.');
    }

    public function show(Request $request, int $customerId, int $id): JsonResponse
    {
        $address = $this->addressService->findOwned($id, $customerId, $request->user()->id);
        return $this->success($address, 'Endereço encontrado.');
    }

    public function update(Request $request, int $customerId, int $id): JsonResponse
    {
        try {
            $this->validate($request, [
                'cep'        => 'sometimes|string|size:8',
                'number'     => 'sometimes|string|max:20',
                'complement' => 'sometimes|nullable|string|max:255',
            ], [
                'cep.string'        => 'O CEP deve ser um texto.',
                'cep.size'          => 'O CEP deve ter exatamente 8 caracteres.',
                'number.string'     => 'O número deve ser um texto.',
                'number.max'        => 'O número deve ter no máximo 20 caracteres.',
                'complement.string' => 'O complemento deve ser um texto.',
                'complement.max'    => 'O complemento deve ter no máximo 255 caracteres.',
            ]);
        } catch (ValidationException $e) {
            return $this->error('Dados inválidos.', 422, $e->errors());
        }

        $address = $this->addressService->update(
            $id,
            $request->only('cep', 'number', 'complement'),
            $customerId,
            $request->user()->id
        );

        return $this->success($address, 'Endereço atualizado com sucesso.');
    }

    public function destroy(Request $request, int $customerId, int $id): JsonResponse
    {
        $this->addressService->delete($id, $customerId, $request->user()->id);
        return $this->noContent();
    }
}
