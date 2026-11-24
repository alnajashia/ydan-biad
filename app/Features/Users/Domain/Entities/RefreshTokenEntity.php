<?php

namespace App\Features\Users\Domain\Entities;

use App\Features\Users\Infrastructure\Persistence\Models\RefreshToken;
use DateTimeImmutable;

final readonly class RefreshTokenEntity
{
    public function __construct(
        public ?int $id,
        public int $userId,
        public string $tokenHash,
        public ?int $accessTokenId,
        public DateTimeImmutable $expiresAt,
        public ?DateTimeImmutable $revokedAt = null,
        public ?DateTimeImmutable $createdAt = null,
    ) {}

    public function isExpired(): bool
    {
        return $this->expiresAt <= new DateTimeImmutable;
    }

    public function isRevoked(): bool
    {
        return $this->revokedAt !== null;
    }

    public function isValid(): bool
    {
        return ! $this->isExpired() && ! $this->isRevoked();
    }

    public static function fromModel(RefreshToken $token): self
    {
        return new self(
            id: $token->id,
            userId: $token->user_id,
            tokenHash: $token->token,
            accessTokenId: $token->access_token_id,
            expiresAt: $token->expires_at->toDateTimeImmutable(),
            revokedAt: $token->revoked_at?->toDateTimeImmutable(),
            createdAt: $token->created_at?->toDateTimeImmutable(),
        );
    }
}
