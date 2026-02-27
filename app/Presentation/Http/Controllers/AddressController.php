<?php

namespace App\Presentation\Http\Controllers;

use App\Application\Services\AddressService;
use App\Presentation\Http\Requests\Address\StoreAddressRequest;
use App\Presentation\Http\Requests\Address\UpdateAddressRequest;
use App\Presentation\Http\Resources\AddressResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class AddressController extends Controller
{
    public function __construct(private readonly AddressService $addressService) {}

    public function index(Request $request, int $customerId): JsonResponse
    {
        $addresses = $this->addressService->listByCustomer($customerId, $request->user()->id);
        return $this->success(AddressResource::collection($addresses), 'Endereços listados com sucesso.');
    }

    public function store(Request $request, int $customerId): JsonResponse
    {
        $rules = new StoreAddressRequest();

        try {
            $this->validate($request, $rules->rules(), $rules->messages());
        } catch (ValidationException $e) {
            return $this->error('Dados inválidos.', 422, $e->errors());
        }

        $address = $this->addressService->create(
            $request->only('cep', 'number', 'complement'),
            $customerId,
            $request->user()->id
        );

        return $this->created(new AddressResource($address), 'Endereço criado com sucesso.');
    }

    public function show(Request $request, int $customerId, int $id): JsonResponse
    {
        $address = $this->addressService->findOwned($id, $customerId, $request->user()->id);
        return $this->success(new AddressResource($address), 'Endereço encontrado.');
    }

    public function update(Request $request, int $customerId, int $id): JsonResponse
    {
        $rules = new UpdateAddressRequest();

        try {
            $this->validate($request, $rules->rules(), $rules->messages());
        } catch (ValidationException $e) {
            return $this->error('Dados inválidos.', 422, $e->errors());
        }

        $address = $this->addressService->update(
            $id,
            $request->only('cep', 'number', 'complement'),
            $customerId,
            $request->user()->id
        );

        return $this->success(new AddressResource($address), 'Endereço atualizado com sucesso.');
    }

    public function destroy(Request $request, int $customerId, int $id): JsonResponse
    {
        $this->addressService->delete($id, $customerId, $request->user()->id);
        return $this->noContent();
    }
}
