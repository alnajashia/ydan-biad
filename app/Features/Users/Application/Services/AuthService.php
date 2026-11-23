<?php

namespace App\Features\Users\Application\Services;

use App\Features\Lookups\Domain\Repositories\RoleRepositoryInterface;
use App\Features\Lookups\Domain\Repositories\StatusRepositoryInterface;
use App\Features\Users\Domain\Repositories\UserRepositoryInterface;
use App\Features\Users\Infrastructure\Persistence\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthService
{
    public function __construct(
        private readonly UserRepositoryInterface $users,
        private readonly StatusRepositoryInterface $statuses,
        private readonly RoleRepositoryInterface $roles,
    ) {}

    /**
     * @param  array<string, mixed>  $data
     * @return array{user: User, token: string}
     */
    public function register(array $data): array
    {
        if (empty($data['status_id'])) {
            $data['status_id'] = $this->statuses->findByName('active')?->id;
        }

        if (empty($data['role_id'])) {
            $data['role_id'] = $this->roles->findByName('customer')?->id;
        }

        $user = $this->users->create($data);

        return [
            'user' => $user,
            'token' => $user->createToken('api')->plainTextToken,
        ];
    }

    /**
     * @return array{user: User, token: string}
     *
     * @throws ValidationException
     */
    public function login(string $phone, string $password): array
    {
        $user = $this->users->findByPhone($phone);

        if ($user === null || ! Hash::check($password, $user->password)) {
            throw ValidationException::withMessages([
                'phone' => ['The provided credentials are incorrect.'],
            ]);
        }

        $status = $user->status;

        if ($status === null || $status->name !== 'active' || $status->value !== true) {
            throw ValidationException::withMessages([
                'phone' => ['This account is not active.'],
            ]);
        }

        return [
            'user' => $user,
            'token' => $user->createToken('api')->plainTextToken,
        ];
    }

    public function logout(User $user): void
    {
        $user->currentAccessToken()?->delete();
    }
}
