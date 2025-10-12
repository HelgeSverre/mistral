<?php

namespace HelgeSverre\Mistral\Dto\Agents;

use Spatie\LaravelData\Attributes\MapName;
use Spatie\LaravelData\Data;

class AgentUpdateRequest extends Data
{
    public function __construct(
        public ?string $name = null,
        public ?string $model = null,
        public ?string $instructions = null,
        public ?string $description = null,
        public ?array $tools = null,
        public ?float $temperature = null,
        #[MapName('top_p')]
        public ?float $topP = null,
    ) {}
}
