<?php

namespace HelgeSverre\Mistral\Dto\Chat;

use Spatie\LaravelData\Data as SpatieData;

class ChatCompletionMessage extends SpatieData
{
    public function __construct(
        public string $role,
        public string $content
    ) {
    }
}
