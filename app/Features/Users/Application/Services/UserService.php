<?php

namespace App\Features\Users\Application\Services;

use App\Features\Lookups\Domain\Repositories\RoleRepositoryInterface;
use App\Features\Lookups\Domain\Repositories\StatusRepositoryInterface;
use App\Features\Users\Domain\Repositories\UserRepositoryInterface;
use App\Features\Users\Infrastructure\Persistence\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\UploadedFile;

class UserService
{
    public function __construct(
        private readonly UserRepositoryInterface $users,
        private readonly StatusRepositoryInterface $statuses,
        private readonly RoleRepositoryInterface $roles,
    ) {}

    public function paginate(int $perPage = 15): LengthAwarePaginator
    {
        return $this->users->paginate($perPage);
    }

    public function find(int $id): ?User
    {
        return $this->users->findById($id);
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function create(array $data): User
    {
        return $this->users->create($this->storeIdentityImages($this->applyDefaults($data)));
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function update(User $user, array $data): User
    {
        return $this->users->update($user, $this->storeIdentityImages($data));
    }

    public function delete(User $user): bool
    {
        return $this->users->delete($user);
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    private function applyDefaults(array $data): array
    {
        if (empty($data['status_id'])) {
            $data['status_id'] = $this->statuses->findByName('active')?->id;
        }

        if (empty($data['role_id'])) {
            $data['role_id'] = $this->roles->findByName('customer')?->id;
        }

        return $data;
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    private function storeIdentityImages(array $data): array
    {
        foreach (['identity_front_image', 'identity_back_image'] as $field) {
            if (($data[$field] ?? null) instanceof UploadedFile) {
                $data[$field] = $data[$field]->store('identity', 'public');
            }
        }

        return $data;
    }
}
