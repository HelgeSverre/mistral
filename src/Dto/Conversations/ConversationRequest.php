<?php

namespace HelgeSverre\Mistral\Dto\Conversations;

use Spatie\LaravelData\Attributes\MapName;
use Spatie\LaravelData\Data as SpatieData;

class ConversationRequest extends SpatieData
{
    public function __construct(
        public ?string $model = null,
        #[MapName('agent_id')]
        public ?string $agentId = null,
        public ?array $messages = null,
        public ?array $tools = null,
        #[MapName('tool_choice')]
        public ?string $toolChoice = null,
        public int|float|null $temperature = null,
        #[MapName('max_tokens')]
        public ?int $maxTokens = null,
        #[MapName('min_tokens')]
        public ?int $minTokens = null,
        #[MapName('top_p')]
        public int|float|null $topP = null,
        #[MapName('random_seed')]
        public ?int $randomSeed = null,
        #[MapName('safe_prompt')]
        public ?bool $safePrompt = null,
        #[MapName('response_format')]
        public ?array $responseFormat = null,
        public ?array $metadata = null,
        public string|array|null $stop = null,
        #[MapName('presence_penalty')]
        public ?float $presencePenalty = null,
        #[MapName('frequency_penalty')]
        public ?float $frequencyPenalty = null,
    ) {}
}
