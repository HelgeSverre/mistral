<?php

namespace HelgeSverre\Mistral\Dto\FineTuning;

use Spatie\LaravelData\Data;

class UpdateFTModelIn extends Data
{
    public function __construct(
        public ?string $name = null,
        public ?string $description = null,
    ) {}
}
