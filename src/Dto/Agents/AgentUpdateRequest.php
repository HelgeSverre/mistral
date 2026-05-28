<?php

namespace HelgeSverre\Mistral\Dto\Agents;

use Spatie\LaravelData\Attributes\MapName;
use Spatie\LaravelData\Data;

class AgentUpdateRequest extends Data
{
    /**
     * @param  array<int, mixed>|null  $tools  List of tools available to the model (function, web_search, web_search_premium, code_interpreter, image_generation, document_library, connector)
     * @param  string[]|null  $handoffs  Agent IDs that this agent can hand off to
     * @param  array<string, mixed>|null  $completionArgs  Completion arguments (temperature, top_p, etc.). Sampling settings live here — there is no top-level temperature/top_p on this endpoint.
     * @param  array<int, mixed>|null  $guardrails  Guardrail configurations applied to this agent
     * @param  array<string, mixed>|null  $metadata  Arbitrary key/value metadata
     * @param  string|null  $versionMessage  Optional message describing this version (max 500 chars per spec)
     */
    public function __construct(
        public ?string $name = null,
        public ?string $model = null,
        public ?string $instructions = null,
        public ?string $description = null,
        public ?array $tools = null,
        public ?array $handoffs = null,
        #[MapName('completion_args')]
        public ?array $completionArgs = null,
        public ?array $guardrails = null,
        public ?array $metadata = null,
        #[MapName('deployment_chat')]
        public ?bool $deploymentChat = null,
        #[MapName('version_message')]
        public ?string $versionMessage = null,
    ) {}
}
