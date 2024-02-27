<?php

namespace HelgeSverre\Mistral\Dto\Chat;

use Spatie\LaravelData\Attributes\MapName;
use Spatie\LaravelData\Data as SpatieData;

class ChatCompletionRequest extends SpatieData
{
    public function __construct(
        public ?string $model = null,
        public ?array $messages = null,
        public int|float|null $temperature = null,
        #[MapName('top_p')]
        public int|float|null $topP = null,
        #[MapName('max_tokens')]
        public ?int $maxTokens = null,
        public ?bool $stream = null,
        #[MapName('safe_prompt')]
        public ?bool $safeMode = null,
        #[MapName('random_seed')]
        public ?int $randomSeed = null,
        ?array $tools = null,
        #[MapName('tool_choice')]
        ?string $toolChoice = null,
        #[MapName('response_format')]
        public ?array $responseFormat = null,
    ) {
    }
}
