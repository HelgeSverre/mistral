<?php

namespace HelgeSverre\Mistral\Dto\Conversations;

use Spatie\LaravelData\Attributes\DataCollectionOf;
use Spatie\LaravelData\Attributes\MapName;
use Spatie\LaravelData\Data as SpatieData;
use Spatie\LaravelData\DataCollection;

class ConversationResponse extends SpatieData
{
    public function __construct(
        #[MapName('conversation_id')]
        public string $conversationId,
        #[DataCollectionOf(ConversationEntry::class)]
        public DataCollection $entries,
    ) {}
}
