<?php

namespace HelgeSverre\Mistral\Dto\Fim;

use HelgeSverre\Mistral\Dto\Chat\ChatCompletionMessage;
use Spatie\LaravelData\Attributes\MapName;
use Spatie\LaravelData\Data as SpatieData;

class FIMChoice extends SpatieData
{
    public function __construct(
        public int $index,
        public ChatCompletionMessage $message,
        #[MapName('finish_reason')]
        public ?string $finishReason = null,
    ) {}

    /**
     * Helper method to get the message content directly
     */
    public function getContent(): string
    {
        return $this->message->content;
    }
}
