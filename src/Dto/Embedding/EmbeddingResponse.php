<?php

namespace HelgeSverre\Mistral\Dto\Embedding;

use HelgeSverre\Mistral\Dto\Usage;
use Spatie\LaravelData\Attributes\DataCollectionOf;
use Spatie\LaravelData\Data as SpatieData;
use Spatie\LaravelData\DataCollection;

class EmbeddingResponse extends SpatieData
{
    public function __construct(
        public ?string $id,
        public ?string $object,
        #[DataCollectionOf(Embedding::class)]
        public DataCollection $data,
        public ?string $model = null,
        public ?Usage $usage = null,
    ) {}
}
