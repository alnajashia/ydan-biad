<?php

namespace App\Features\Users\Infrastructure\Persistence\Repositories;

use App\Features\Users\Domain\Entities\UserEntity;
use App\Features\Users\Domain\Repositories\UserRepositoryInterface;
use App\Features\Users\Domain\ValueObjects\PhoneNumber;
use App\Features\Users\Infrastructure\Persistence\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Laravel\Sanctum\PersonalAccessToken;

class EloquentUserRepository implements UserRepositoryInterface
{
    /**
     * @param  array<string, mixed>  $data
     */
    public function create(array $data): UserEntity
    {
        $model = User::query()->create($data);

        return UserEntity::fromModel($model->refresh());
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function update(UserEntity $user, array $data): UserEntity
    {
        $model = User::query()->findOrFail($user->id);
        $oldFrontImage = $model->identity_front_image;
        $oldBackImage = $model->identity_back_image;

        $model->update($data);
        $model->refresh();

        $this->deleteReplacedImages($oldFrontImage, $oldBackImage, $model);

        return UserEntity::fromModel($model);
    }

    public function delete(UserEntity $user): bool
    {
        $model = User::query()->find($user->id);

        if ($model === null) {
            return false;
        }

        $this->revokeAllAccessTokens($user->id);
        Storage::disk('public')->delete(array_filter([
            $model->identity_front_image,
            $model->identity_back_image,
        ]));

        return (bool) $model->delete();
    }

    public function findById(int $id): ?UserEntity
    {
        $model = User::query()->with('customer')->find($id);

        if ($model === null) {
            return null;
        }

        return UserEntity::fromModel($model);
    }

    public function findByPhone(PhoneNumber $phone): ?UserEntity
    {
        $model = User::query()->with('customer')->where('phone', $phone->value)->first();

        if ($model === null) {
            return null;
        }

        return UserEntity::fromModel($model);
    }

    public function findByCredentials(PhoneNumber $phone, string $password): ?UserEntity
    {
        $model = User::query()->with('customer')->where('phone', $phone->value)->first();

        if ($model === null || ! Hash::check($password, $model->password)) {
            return null;
        }

        return UserEntity::fromModel($model);
    }

    /**
     * Delete the old identity images when they were replaced by new ones.
     */
    private function deleteReplacedImages(?string $oldFrontImage, ?string $oldBackImage, User $model): void
    {
        foreach (['identity_front_image' => $oldFrontImage, 'identity_back_image' => $oldBackImage] as $field => $oldImage) {
            if ($oldImage !== null && $oldImage !== $model->{$field}) {
                Storage::disk('public')->delete($oldImage);
            }
        }
    }

    /**
     * @return LengthAwarePaginator<int, UserEntity>
     */
    public function paginate(int $perPage = 15): LengthAwarePaginator
    {
        $paginator = User::query()->with('customer')->latest()->paginate($perPage);

        $paginator->setCollection(
            $paginator->getCollection()->map(fn (User $model): UserEntity => UserEntity::fromModel($model)),
        );

        return $paginator;
    }

    /**
     * @return array{token: string, id: int}
     */
    public function issueAccessToken(UserEntity $user): array
    {
        $model = User::query()->findOrFail($user->id);
        $token = $model->createToken('api');

        return [
            'token' => $token->plainTextToken,
            'id' => (int) $token->accessToken->id,
        ];
    }

    public function revokeAccessTokenById(int $id): void
    {
        PersonalAccessToken::query()->where('id', $id)->delete();
    }

    public function revokeAllAccessTokens(int $userId): void
    {
        PersonalAccessToken::query()
            ->where('tokenable_type', User::class)
            ->where('tokenable_id', $userId)
            ->delete();
    }
}
