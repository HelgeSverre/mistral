<?php

namespace HelgeSverre\Mistral\Dto\Fim;

use Spatie\LaravelData\Attributes\MapName;
use Spatie\LaravelData\Data as SpatieData;

class StreamedFIMChoice extends SpatieData
{
    public function __construct(
        public int $index,
        public StreamedFIMDelta $delta,
        #[MapName('finish_reason')]
        public ?string $finishReason,
    ) {}
}
