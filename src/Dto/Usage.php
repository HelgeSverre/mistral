<?php

namespace HelgeSverre\Mistral\Dto;

use Spatie\LaravelData\Attributes\MapName;
use Spatie\LaravelData\Data as SpatieData;

class Usage extends SpatieData
{
    public function __construct(
        #[MapName('prompt_tokens')]
        public int $promptTokens,
        #[MapName('completion_tokens')]
        public int $completionTokens,
        #[MapName('total_tokens')]
        public int $totalTokens,
    ) {}
}
