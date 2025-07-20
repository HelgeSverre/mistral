<?php

namespace HelgeSverre\Mistral\Dto\Embedding;

use Spatie\LaravelData\Data as SpatieData;

class Embedding extends SpatieData
{
    public function __construct(
        public ?string $object = null,
        public ?array $embedding = null,
    ) {}
}
