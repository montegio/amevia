<?php

namespace App\Domain\Family\DTOs;

final readonly class CreateFamilyData
{
    public function __construct(
        public string $name,
        public string $ownerUserId,
        public ?string $slug = null,
        public string $timezone = 'America/Sao_Paulo',
        public string $locale = 'pt-BR',
        public string $status = 'active',
    ) {
    }
}
