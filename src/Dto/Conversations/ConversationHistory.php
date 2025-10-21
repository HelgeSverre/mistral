<?php

namespace HelgeSverre\Mistral\Dto\Conversations;

use Spatie\LaravelData\Attributes\DataCollectionOf;
use Spatie\LaravelData\Data as SpatieData;
use Spatie\LaravelData\DataCollection;

class ConversationHistory extends SpatieData
{
    public function __construct(
        #[DataCollectionOf(ConversationEntry::class)]
        public DataCollection $entries,
    ) {}
}
