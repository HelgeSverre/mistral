<?php

namespace HelgeSverre\Mistral\Dto\FineTuning;

use Spatie\LaravelData\Attributes\MapName;
use Spatie\LaravelData\Data;

class ClassifierFTModelOut extends Data
{
    public function __construct(
        public string $id,
        public string $object,
        #[MapName('model_type')]
        public string $modelType,
        #[MapName('created_at')]
        public int $createdAt,
        public string $name,
        public ?string $description = null,
        public ?string $job = null,
        #[MapName('root_model')]
        public ?string $rootModel = null,
        public ?bool $archived = null,
    ) {}
}
