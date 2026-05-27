<?php

namespace HelgeSverre\Mistral\Dto\Agents;

use Spatie\LaravelData\Attributes\MapName;
use Spatie\LaravelData\Data;

class AgentAliasResponse extends Data
{
    public function __construct(
        public string $alias,
        public int $version,
        #[MapName('created_at')]
        public string $createdAt,
        #[MapName('updated_at')]
        public string $updatedAt,
    ) {}
}
