<?php

namespace HelgeSverre\Mistral\Dto\Fim;

use Spatie\LaravelData\Attributes\MapName;
use Spatie\LaravelData\Data as SpatieData;

class FIMChoice extends SpatieData
{
    public function __construct(
        public int $index,
        public string $message,
        #[MapName('finish_reason')]
        public ?string $finishReason = null,
    ) {}
}
