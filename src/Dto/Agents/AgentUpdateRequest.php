<?php

namespace HelgeSverre\Mistral\Dto\Agents;

use Spatie\LaravelData\Attributes\MapName;
use Spatie\LaravelData\Data;

class AgentUpdateRequest extends Data
{
    /**
     * @param  string[]|null  $handoffs  Agent IDs that this agent can hand off to
     */
    public function __construct(
        public ?string $name = null,
        public ?string $model = null,
        public ?string $instructions = null,
        public ?string $description = null,
        public ?array $tools = null,
        public ?array $handoffs = null,
        public ?float $temperature = null,
        #[MapName('top_p')]
        public ?float $topP = null,
    ) {}
}
