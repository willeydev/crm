<?php

namespace App\Presentation\Http\Controllers;

use App\Application\Services\AuthService;
use App\Domain\Exceptions\EmailAlreadyExistsException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function __construct(private readonly AuthService $authService) {}

    public function register(Request $request): JsonResponse
    {
        try {
            $this->validate($request, [
                'name'     => 'required|string|max:255',
                'email'    => 'required|email',
                'password' => 'required|string|min:8|confirmed',
            ], [
                'name.required'      => 'O nome é obrigatório.',
                'name.string'        => 'O nome deve ser um texto.',
                'name.max'           => 'O nome deve ter no máximo 255 caracteres.',
                'email.required'     => 'O e-mail é obrigatório.',
                'email.email'        => 'Informe um e-mail válido.',
                'password.required'  => 'A senha é obrigatória.',
                'password.string'    => 'A senha deve ser um texto.',
                'password.min'       => 'A senha deve ter no mínimo 8 caracteres.',
                'password.confirmed' => 'A confirmação de senha não confere.',
            ]);
        } catch (ValidationException $e) {
            return $this->error('Dados inválidos.', 422, $e->errors());
        }

        try {
            $result = $this->authService->register($request->only('name', 'email', 'password'));
        } catch (EmailAlreadyExistsException $e) {
            return $this->error($e->getMessage(), 409);
        }

        return $this->created([
            'user'  => $result['user'],
            'token' => $result['token'],
        ], 'Usuário registrado com sucesso.');
    }

    public function login(Request $request): JsonResponse
    {
        try {
            $this->validate($request, [
                'email'    => 'required|email',
                'password' => 'required|string',
            ], [
                'email.required'    => 'O e-mail é obrigatório.',
                'email.email'       => 'Informe um e-mail válido.',
                'password.required' => 'A senha é obrigatória.',
                'password.string'   => 'A senha deve ser um texto.',
            ]);
        } catch (ValidationException $e) {
            return $this->error('Dados inválidos.', 422, $e->errors());
        }

        $result = $this->authService->login($request->input('email'), $request->input('password'));

        if (! $result) {
            return $this->error('Credenciais inválidas.', 401);
        }

        return $this->success([
            'user'  => $result['user'],
            'token' => $result['token'],
        ], 'Login realizado com sucesso.');
    }

    public function me(Request $request): JsonResponse
    {
        return $this->success($request->user(), 'Dados do usuário autenticado.');
    }

    public function logout(): JsonResponse
    {
        return $this->success(null, 'Logout realizado com sucesso.');
    }
}
