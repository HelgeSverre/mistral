<?php

namespace HelgeSverre\Mistral\Dto\Conversations;

use Spatie\LaravelData\Attributes\MapName;
use Spatie\LaravelData\Data as SpatieData;

class ConversationList extends SpatieData
{
    public function __construct(
        public string $object,
        public array $data,
        #[MapName('has_more')]
        public bool $hasMore,
        #[MapName('first_id')]
        public ?string $firstId = null,
        #[MapName('last_id')]
        public ?string $lastId = null,
    ) {}
}
