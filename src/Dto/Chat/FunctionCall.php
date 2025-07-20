<?php

namespace HelgeSverre\Mistral\Dto\Chat;

use Spatie\LaravelData\Data as SpatieData;

class FunctionCall extends SpatieData
{
    public function __construct(
        public string $name,
        public string $arguments,
    ) {}

    public function args(): ?array
    {
        return json_decode($this->arguments, true);
    }
}
