<?php

namespace HelgeSverre\Mistral\Dto\Models;

use Spatie\LaravelData\Attributes\MapName;
use Spatie\LaravelData\Data as SpatieData;

/**
 * Base Model Card
 */
class BaseModelCard extends SpatieData
{
    public function __construct(
        public string $id,
        public ModelCapabilities $capabilities,
        public ?string $object = 'model',
        public ?int $created = null,
        #[MapName('owned_by')]
        public ?string $ownedBy = 'mistralai',
        public ?string $name = null,
        public ?string $description = null,
        #[MapName('max_context_length')]
        public int $maxContextLength = 32768,
        /** @var array<string> */
        public array $aliases = [],
        public ?string $deprecation = null,
        #[MapName('deprecation_replacement_model')]
        public ?string $deprecationReplacementModel = null,
        #[MapName('default_model_temperature')]
        public ?float $defaultModelTemperature = null,
        public string $type = 'base',
    ) {}
}
