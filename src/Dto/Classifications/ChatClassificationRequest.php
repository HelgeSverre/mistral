<?php

namespace HelgeSverre\Mistral\Dto\Classifications;

use Spatie\LaravelData\Data as SpatieData;

class ChatClassificationRequest extends SpatieData
{
    public function __construct(
        public string $model,
        public array $messages,
    ) {}
}
