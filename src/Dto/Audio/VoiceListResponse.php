<?php

namespace HelgeSverre\Mistral\Dto\Audio;

use Spatie\LaravelData\Attributes\DataCollectionOf;
use Spatie\LaravelData\Attributes\MapName;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\DataCollection;

class VoiceListResponse extends Data
{
    public function __construct(
        #[DataCollectionOf(VoiceResponse::class)]
        public DataCollection $items,
        public int $total,
        public int $page,
        #[MapName('page_size')]
        public int $pageSize,
        #[MapName('total_pages')]
        public int $totalPages,
    ) {}
}
