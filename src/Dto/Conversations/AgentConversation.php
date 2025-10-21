<?php

namespace HelgeSverre\Mistral\Dto\Conversations;

use Spatie\LaravelData\Attributes\MapName;
use Spatie\LaravelData\Data as SpatieData;

class AgentConversation extends SpatieData
{
    public function __construct(
        public string $id,
        public string $object,
        #[MapName('created_at')]
        public int $createdAt,
        #[MapName('agent_id')]
        public string $agentId,
        public ?array $metadata = null,
    ) {}
}
