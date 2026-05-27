<?php

namespace HelgeSverre\Mistral\Dto\Agents;

use Spatie\LaravelData\Attributes\MapName;
use Spatie\LaravelData\Data;

class Agent extends Data
{
    /**
     * @param  array<int, mixed>|null  $tools  List of tools available to the model (function, web_search, web_search_premium, code_interpreter, image_generation, document_library, connector)
     * @param  array<int, string>|null  $handoffs  Agent IDs that this agent can hand off to
     * @param  array<int, int>|null  $versions  All version numbers that exist for this agent
     * @param  array<string, mixed>|null  $completionArgs  Completion arguments (temperature, top_p, etc.) — replaces top-level temperature/topP
     * @param  array<int, mixed>|null  $guardrails  Guardrail configurations applied to this agent
     * @param  array<string, mixed>|null  $metadata  Arbitrary key/value metadata
     * @param  int|string|null  $createdAt  Spec says ISO date-time string; legacy/older responses returned a Unix timestamp int. Both accepted.
     * @param  string|null  $updatedAt  ISO date-time when the agent (current version) was last updated
     * @param  string|null  $source  Where the agent originated (e.g. "api", "le-chat")
     */
    public function __construct(
        public string $id,
        public string $object,
        public string $name,
        public string $model,
        #[MapName('created_at')]
        public int|string|null $createdAt = null,
        public ?string $instructions = null,
        public ?string $description = null,
        public ?array $tools = null,
        public ?array $handoffs = null,
        public ?float $temperature = null,
        #[MapName('top_p')]
        public ?float $topP = null,
        public int $version = 1,
        public ?array $versions = null,
        #[MapName('updated_at')]
        public ?string $updatedAt = null,
        #[MapName('deployment_chat')]
        public ?bool $deploymentChat = null,
        public ?string $source = null,
        #[MapName('version_message')]
        public ?string $versionMessage = null,
        #[MapName('completion_args')]
        public ?array $completionArgs = null,
        public ?array $guardrails = null,
        public ?array $metadata = null,
    ) {}
}
