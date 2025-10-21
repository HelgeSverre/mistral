<?php

namespace HelgeSverre\Mistral\Dto\FineTuning;

use Spatie\LaravelData\Data;

class ValidationError extends Data
{
    public function __construct(
        public string $error,
    ) {}
}
