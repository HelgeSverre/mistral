<?php

namespace HelgeSverre\Mistral\Dto\Embedding;

use Spatie\LaravelData\Attributes\MapName;
use Spatie\LaravelData\Data as SpatieData;

class EmbeddingRequest extends SpatieData
{
    public function __construct(
        public ?string $model = null,
        public ?array $input = null,
        #[MapName('encoding_format')]
        public ?string $encodingFormat = null,
    ) {
    }
}
