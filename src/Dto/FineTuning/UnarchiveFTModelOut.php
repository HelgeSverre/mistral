<?php

namespace HelgeSverre\Mistral\Dto\FineTuning;

use Spatie\LaravelData\Attributes\MapName;
use Spatie\LaravelData\Data;

class UnarchiveFTModelOut extends Data
{
    public function __construct(
        public string $id,
        public string $object,
        public bool $archived,
        #[MapName('model_type')]
        public ?string $modelType = null,
    ) {}
}
