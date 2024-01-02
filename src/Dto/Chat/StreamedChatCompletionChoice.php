<?php

namespace HelgeSverre\Mistral\Dto\Chat;

use Spatie\LaravelData\Attributes\MapName;
use Spatie\LaravelData\Data as SpatieData;

class StreamedChatCompletionChoice extends SpatieData
{
    public function __construct(
        public int $index,
        public StreamedChatCompletionDelta $delta,

        #[MapName('finish_reason')]
        public ?string $finishReason,
    ) {
    }
}
