<?php

namespace HelgeSverre\Mistral\Dto\Conversations;

use Spatie\LaravelData\Attributes\MapName;
use Spatie\LaravelData\Data as SpatieData;

class ConversationEntry extends SpatieData
{
    public function __construct(
        public string $id,
        public string $object,
        #[MapName('created_at')]
        public int $createdAt,
        public string $role,
        public ?string $content = null,
        #[MapName('tool_calls')]
        public ?array $toolCalls = null,
        #[MapName('tool_call_id')]
        public ?string $toolCallId = null,
        public ?string $name = null,
        public ?array $metadata = null,
    ) {}
}
