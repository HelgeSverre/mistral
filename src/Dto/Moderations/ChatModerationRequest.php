<?php

namespace HelgeSverre\Mistral\Dto\Moderations;

use Spatie\LaravelData\Data as SpatieData;

class ChatModerationRequest extends SpatieData
{
    public function __construct(
        public string $model,
        public array $messages,
    ) {}
}
