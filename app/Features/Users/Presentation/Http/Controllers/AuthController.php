<?php

namespace App\Features\Users\Presentation\Http\Controllers;

use App\Features\Users\Application\DTOs\LoginDTO;
use App\Features\Users\Application\DTOs\RegisterUserDTO;
use App\Features\Users\Application\UseCases\LoginUserUseCase;
use App\Features\Users\Application\UseCases\LogoutUserUseCase;
use App\Features\Users\Application\UseCases\RefreshTokenUseCase;
use App\Features\Users\Application\UseCases\RegisterUserUseCase;
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
        private readonly RegisterUserUseCase $registerUser,
        private readonly LoginUserUseCase $loginUser,
        private readonly LogoutUserUseCase $logoutUser,
        private readonly RefreshTokenUseCase $refreshToken,
    ) {}

    public function register(RegisterRequest $request): JsonResponse
    {
        $user = $this->registerUser->execute(RegisterUserDTO::fromRequest($request));

        return response()->json([
            'user' => new UserResource($user),
            'message' => 'Registration successful. Your account is pending activation.',
        ], 201);
    }

    public function login(LoginRequest $request): JsonResponse
    {
        $result = $this->loginUser->execute(LoginDTO::fromRequest($request));

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
        $result = $this->refreshToken->execute($request->validated('refresh_token'));

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
        $this->logoutUser->execute($request->user()->currentAccessToken()?->id);

        return response()->json(['message' => 'Logged out.']);
    }
}
