<?php

namespace App\Features\Users\Presentation\Http\Controllers;

use App\Features\Users\Application\Services\AuthService;
use App\Features\Users\Application\Services\UserService;
use App\Features\Users\Presentation\Http\Requests\LoginRequest;
use App\Features\Users\Presentation\Http\Requests\RegisterRequest;
use App\Features\Users\Presentation\Http\Resources\UserResource;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AuthController extends Controller
{
    public function __construct(
        private readonly AuthService $authService,
        private readonly UserService $userService,
    ) {}

    public function register(RegisterRequest $request): JsonResponse
    {
        $data = $this->userService->create($request->validated());

        return response()->json([
            'user' => new UserResource($data),
            'token' => $data->createToken('api')->plainTextToken,
        ], 201);
    }

    public function login(LoginRequest $request): JsonResponse
    {
        $result = $this->authService->login($request->validated('phone'), $request->validated('password'));

        return response()->json([
            'user' => new UserResource($result['user']),
            'token' => $result['token'],
        ]);
    }

    public function me(Request $request): JsonResponse
    {
        return response()->json([
            'user' => new UserResource($request->user()->load(['customer', 'status', 'role'])),
        ]);
    }

    public function logout(Request $request): JsonResponse
    {
        $this->authService->logout($request->user());

        return response()->json(['message' => 'Logged out.']);
    }
}
