<?php

namespace HelgeSverre\Mistral\Dto\FineTuning;

use Spatie\LaravelData\Data;

class WandbIntegration extends Data
{
    public function __construct(
        public string $project,
        public ?string $name = null,
        public ?string $key = null,
    ) {}
}
