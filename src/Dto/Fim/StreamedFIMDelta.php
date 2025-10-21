<?php

namespace HelgeSverre\Mistral\Dto\Fim;

use Spatie\LaravelData\Data as SpatieData;

class StreamedFIMDelta extends SpatieData
{
    public function __construct(
        public ?string $content
    ) {}
}
