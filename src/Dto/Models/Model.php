<?php

namespace HelgeSverre\Mistral\Dto\Models;

use Spatie\LaravelData\Attributes\DataCollectionOf;
use Spatie\LaravelData\Attributes\MapName;
use Spatie\LaravelData\Data as SpatieData;

/**
 * Model
 */
class Model extends SpatieData
{
    public function __construct(
        public ?string $id = null,
        public ?string $object = null,
        public ?int $created = null,
        #[MapName('owned_by')]
        public ?string $ownedBy = null,
        public ?string $root = null,
        public ?string $parent = null,
        #[DataCollectionOf(ModelPermission::class)]
        public ?array $permission = null

    ) {
    }
}
