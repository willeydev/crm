<?php

namespace App\Presentation\Http\Controllers;

use App\Application\Services\CustomerService;
use App\Presentation\Http\Requests\Customer\StoreCustomerRequest;
use App\Presentation\Http\Requests\Customer\UpdateCustomerRequest;
use App\Presentation\Http\Resources\CustomerResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class CustomerController extends Controller
{
    public function __construct(private readonly CustomerService $customerService) {}

    public function index(Request $request): JsonResponse
    {
        $perPage   = max(1, min(100, (int) $request->query('per_page', 15)));
        $paginator = $this->customerService->listByUser($request->user()->id, $perPage);

        return $this->paginated(
            $paginator,
            CustomerResource::collection($paginator->items()),
            'Clientes listados com sucesso.'
        );
    }

    public function store(Request $request): JsonResponse
    {
        $rules = new StoreCustomerRequest();

        try {
            $this->validate($request, $rules->rules(), $rules->messages());
        } catch (ValidationException $e) {
            return $this->error('Dados inválidos.', 422, $e->errors());
        }

        $customer = $this->customerService->create(
            $request->only('name', 'email', 'phone', 'document'),
            $request->user()->id
        );

        return $this->created(new CustomerResource($customer), 'Cliente criado com sucesso.');
    }

    public function show(Request $request, int $id): JsonResponse
    {
        $customer = $this->customerService->findOwned($id, $request->user()->id);
        return $this->success(new CustomerResource($customer), 'Cliente encontrado.');
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $rules = new UpdateCustomerRequest();

        try {
            $this->validate($request, $rules->rules(), $rules->messages());
        } catch (ValidationException $e) {
            return $this->error('Dados inválidos.', 422, $e->errors());
        }

        $customer = $this->customerService->update(
            $id,
            $request->only('name', 'email', 'phone', 'document'),
            $request->user()->id
        );

        return $this->success(new CustomerResource($customer), 'Cliente atualizado com sucesso.');
    }

    public function destroy(Request $request, int $id): JsonResponse
    {
        $this->customerService->delete($id, $request->user()->id);
        return $this->noContent();
    }
}
