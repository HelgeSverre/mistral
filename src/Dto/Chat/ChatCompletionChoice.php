<?php

namespace HelgeSverre\Mistral\Dto\Chat;

use Spatie\LaravelData\Attributes\MapName;
use Spatie\LaravelData\Data as SpatieData;

class ChatCompletionChoice extends SpatieData
{
    public function __construct(
        public int $index,
        public ChatCompletionMessage $message,

        #[MapName('finish_reason')]
        public ?string $finishReason,
    ) {}
}
