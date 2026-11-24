<?php

namespace App\Features\Users\Presentation\Http\Controllers;

use App\Features\Users\Application\DTOs\LoginDTO;
use App\Features\Users\Application\DTOs\RegisterUserDTO;
use App\Features\Users\Application\Services\AuthService;
use App\Features\Users\Domain\Entities\UserEntity;
use App\Features\Users\Presentation\Http\Requests\LoginRequest;
use App\Features\Users\Presentation\Http\Requests\RefreshTokenRequest;
use App\Features\Users\Presentation\Http\Requests\RegisterRequest;
use App\Features\Users\Presentation\Http\Resources\UserResource;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AuthController extends Controller
{
    public function __construct(
        private readonly AuthService $authService,
    ) {}

    public function register(RegisterRequest $request): JsonResponse
    {
        $validated= $request->validated();
        $user = $this->authService->register(RegisterUserDTO::toDto($validated));

        return response()->json([
            'user' => new UserResource($user),
            'message' => 'Registration successful. Your account is pending activation.',
        ], 201);
    }

    public function login(LoginRequest $request): JsonResponse
    {
        $result = $this->authService->login(LoginDTO::fromRequest($request));

        return response()->json([
            'user' => new UserResource($result['user']),
            'access_token' => $result['access_token'],
            'refresh_token' => $result['refresh_token'],
            'token_type' => $result['token_type'],
            'expires_in' => $result['expires_in'],
        ]);
    }

    public function refresh(RefreshTokenRequest $request): JsonResponse
    {
        $result = $this->authService->refresh($request->validated('refresh_token'));

        return response()->json([
            'user' => new UserResource($result['user']),
            'access_token' => $result['access_token'],
            'refresh_token' => $result['refresh_token'],
            'token_type' => $result['token_type'],
            'expires_in' => $result['expires_in'],
        ]);
    }

    public function me(Request $request): JsonResponse
    {
        return response()->json([
            'user' => new UserResource(UserEntity::fromModel($request->user()->load('customer'))),
        ]);
    }

    public function logout(Request $request): JsonResponse
    {
        $this->authService->logout($request->user()->currentAccessToken()?->id);

        return response()->json(['message' => 'Logged out.']);
    }

    public function logoutEverywhere(Request $request): JsonResponse
    {
        $this->authService->logoutEverywhere($request->user()->id);

        return response()->json(['message' => 'Logged out from all devices.']);
    }
}
