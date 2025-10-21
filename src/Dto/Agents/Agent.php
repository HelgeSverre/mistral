<?php

namespace HelgeSverre\Mistral\Dto\Agents;

use Spatie\LaravelData\Attributes\MapName;
use Spatie\LaravelData\Data;

class Agent extends Data
{
    public function __construct(
        public string $id,
        public string $object,
        #[MapName('created_at')]
        public int $createdAt,
        public string $name,
        public string $model,
        public ?string $instructions = null,
        public ?string $description = null,
        public ?array $tools = null,
        public ?float $temperature = null,
        #[MapName('top_p')]
        public ?float $topP = null,
        public int $version = 1,
    ) {}
}
