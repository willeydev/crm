<?php

namespace App\Presentation\Http\Controllers;

use App\Application\Services\CustomerService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class CustomerController extends Controller
{
    public function __construct(private readonly CustomerService $customerService) {}

    public function index(Request $request): JsonResponse
    {
        $customers = $this->customerService->listByUser($request->user()->id);
        return $this->success($customers, 'Clientes listados com sucesso.');
    }

    public function store(Request $request): JsonResponse
    {
        try {
            $this->validate($request, [
                'name'     => 'required|string|max:255',
                'email'    => 'nullable|email|max:255',
                'phone'    => 'nullable|string|max:20',
                'document' => 'nullable|string|max:20',
            ], [
                'name.required'   => 'O nome é obrigatório.',
                'name.string'     => 'O nome deve ser um texto.',
                'name.max'        => 'O nome deve ter no máximo 255 caracteres.',
                'email.email'     => 'Informe um e-mail válido.',
                'email.max'       => 'O e-mail deve ter no máximo 255 caracteres.',
                'phone.string'    => 'O telefone deve ser um texto.',
                'phone.max'       => 'O telefone deve ter no máximo 20 caracteres.',
                'document.string' => 'O documento deve ser um texto.',
                'document.max'    => 'O documento deve ter no máximo 20 caracteres.',
            ]);
        } catch (ValidationException $e) {
            return $this->error('Dados inválidos.', 422, $e->errors());
        }

        $customer = $this->customerService->create(
            $request->only('name', 'email', 'phone', 'document'),
            $request->user()->id
        );

        return $this->created($customer, 'Cliente criado com sucesso.');
    }

    public function show(Request $request, int $id): JsonResponse
    {
        $customer = $this->customerService->findOwned($id, $request->user()->id);
        return $this->success($customer, 'Cliente encontrado.');
    }

    public function update(Request $request, int $id): JsonResponse
    {
        try {
            $this->validate($request, [
                'name'     => 'sometimes|string|max:255',
                'email'    => 'sometimes|nullable|email|max:255',
                'phone'    => 'sometimes|nullable|string|max:20',
                'document' => 'sometimes|nullable|string|max:20',
            ], [
                'name.string'     => 'O nome deve ser um texto.',
                'name.max'        => 'O nome deve ter no máximo 255 caracteres.',
                'email.email'     => 'Informe um e-mail válido.',
                'email.max'       => 'O e-mail deve ter no máximo 255 caracteres.',
                'phone.string'    => 'O telefone deve ser um texto.',
                'phone.max'       => 'O telefone deve ter no máximo 20 caracteres.',
                'document.string' => 'O documento deve ser um texto.',
                'document.max'    => 'O documento deve ter no máximo 20 caracteres.',
            ]);
        } catch (ValidationException $e) {
            return $this->error('Dados inválidos.', 422, $e->errors());
        }

        $customer = $this->customerService->update(
            $id,
            $request->only('name', 'email', 'phone', 'document'),
            $request->user()->id
        );

        return $this->success($customer, 'Cliente atualizado com sucesso.');
    }

    public function destroy(Request $request, int $id): JsonResponse
    {
        $this->customerService->delete($id, $request->user()->id);
        return $this->noContent();
    }
}
