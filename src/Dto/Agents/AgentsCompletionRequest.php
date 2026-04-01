<?php

declare(strict_types=1);

namespace HelgeSverre\Mistral\Dto\Agents;

use Spatie\LaravelData\Attributes\MapName;
use Spatie\LaravelData\Data;

class AgentsCompletionRequest extends Data
{
    public function __construct(
        #[MapName('agent_id')]
        public string $agentId,
        public array $messages,
        #[MapName('max_tokens')]
        public ?int $maxTokens = null,
        public ?bool $stream = null,
        public string|array|null $stop = null,
        #[MapName('random_seed')]
        public ?int $randomSeed = null,
        #[MapName('response_format')]
        public ?array $responseFormat = null,
        public ?array $tools = null,
        #[MapName('tool_choice')]
        public ?string $toolChoice = null,
        #[MapName('presence_penalty')]
        public ?float $presencePenalty = null,
        #[MapName('frequency_penalty')]
        public ?float $frequencyPenalty = null,
        public ?int $n = null,
        public ?array $prediction = null,
        #[MapName('parallel_tool_calls')]
        public ?bool $parallelToolCalls = null,
        #[MapName('prompt_mode')]
        public ?string $promptMode = null,
    ) {}
}
