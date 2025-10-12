<?php

namespace HelgeSverre\Mistral\Dto\Fim;

use HelgeSverre\Mistral\Dto\Usage;
use Spatie\LaravelData\Attributes\DataCollectionOf;
use Spatie\LaravelData\Data as SpatieData;
use Spatie\LaravelData\DataCollection;

class FIMCompletionResponse extends SpatieData
{
    public function __construct(
        public string $id,
        public string $object,
        public int $created,
        public string $model,
        #[DataCollectionOf(FIMChoice::class)]
        public DataCollection $choices,
        public Usage $usage,
    ) {}
}
