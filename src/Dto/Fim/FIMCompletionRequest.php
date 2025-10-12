<?php

namespace HelgeSverre\Mistral\Dto\Fim;

use Spatie\LaravelData\Attributes\MapName;
use Spatie\LaravelData\Data as SpatieData;

class FIMCompletionRequest extends SpatieData
{
    public function __construct(
        public string $model,
        public string $prompt,
        public ?string $suffix = null,
        public int|float|null $temperature = null,
        #[MapName('top_p')]
        public int|float|null $topP = null,
        #[MapName('max_tokens')]
        public ?int $maxTokens = null,
        #[MapName('min_tokens')]
        public ?int $minTokens = null,
        public ?bool $stream = null,
        #[MapName('random_seed')]
        public ?int $randomSeed = null,
        public string|array|null $stop = null,
    ) {}
}
