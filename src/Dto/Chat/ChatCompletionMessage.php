<?php

namespace HelgeSverre\Mistral\Dto\Chat;

use Spatie\LaravelData\Attributes\DataCollectionOf;
use Spatie\LaravelData\Attributes\MapName;
use Spatie\LaravelData\Data as SpatieData;
use Spatie\LaravelData\DataCollection;

class ChatCompletionMessage extends SpatieData
{
    public function __construct(
        public string $role,
        public string $content,
        #[MapName('tool_calls')]
        #[DataCollectionOf(ToolCalls::class)]
        public ?DataCollection $toolCalls = null,
    ) {}
}
