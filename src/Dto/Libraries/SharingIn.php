<?php

declare(strict_types=1);

namespace HelgeSverre\Mistral\Dto\Libraries;

use HelgeSverre\Mistral\Enums\AccessRole;
use HelgeSverre\Mistral\Enums\EntityType;
use Spatie\LaravelData\Attributes\MapName;
use Spatie\LaravelData\Data;

class SharingIn extends Data
{
    public function __construct(
        #[MapName('entity_id')]
        public string $entityId,
        #[MapName('entity_type')]
        public EntityType $entityType,
        public AccessRole $role,
    ) {}
}
