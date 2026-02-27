<?php

namespace App\Presentation\Http\Controllers;

use App\Application\Services\AuthService;
use App\Domain\Exceptions\EmailAlreadyExistsException;
use App\Presentation\Http\Requests\Auth\LoginRequest;
use App\Presentation\Http\Requests\Auth\RegisterRequest;
use App\Presentation\Http\Resources\UserResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function __construct(private readonly AuthService $authService) {}

    public function register(RegisterRequest $request): JsonResponse
    {
        try {
            $this->validate($request, $request->rules(), $request->messages());
        } catch (ValidationException $e) {
            return $this->error('Dados inválidos.', 422, $e->errors());
        }

        try {
            $result = $this->authService->register($request->only('name', 'email', 'password'));
        } catch (EmailAlreadyExistsException $e) {
            return $this->error($e->getMessage(), 409);
        }

        return $this->created([
            'user'  => new UserResource($result['user']),
            'token' => $result['token'],
        ], 'Usuário registrado com sucesso.');
    }

    public function login(LoginRequest $request): JsonResponse
    {
        try {
            $this->validate($request, $request->rules(), $request->messages());
        } catch (ValidationException $e) {
            return $this->error('Dados inválidos.', 422, $e->errors());
        }

        $result = $this->authService->login($request->input('email'), $request->input('password'));

        if (! $result) {
            return $this->error('Credenciais inválidas.', 401);
        }

        return $this->success([
            'user'  => new UserResource($result['user']),
            'token' => $result['token'],
        ], 'Login realizado com sucesso.');
    }

    public function me(Request $request): JsonResponse
    {
        return $this->success(new UserResource($request->user()), 'Dados do usuário autenticado.');
    }

    public function logout(): JsonResponse
    {
        return $this->success(null, 'Logout realizado com sucesso.');
    }
}
