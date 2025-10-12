<?php

namespace HelgeSverre\Mistral\Dto\Classifications;

use Spatie\LaravelData\Data as SpatieData;

class ClassificationRequest extends SpatieData
{
    public function __construct(
        public string $model,
        public string|array $input,
    ) {}
}
