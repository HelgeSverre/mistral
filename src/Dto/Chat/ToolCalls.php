<?php

namespace HelgeSverre\Mistral\Dto\Chat;

use Spatie\LaravelData\Data as SpatieData;

class ToolCalls extends SpatieData
{
    public function __construct(
        public string $id,
        public FunctionCall $function,
        public ?string $type = 'function',
    ) {}
}
