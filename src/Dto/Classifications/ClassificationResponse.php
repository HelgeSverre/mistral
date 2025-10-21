<?php

namespace HelgeSverre\Mistral\Dto\Classifications;

use Spatie\LaravelData\Attributes\DataCollectionOf;
use Spatie\LaravelData\Data as SpatieData;
use Spatie\LaravelData\DataCollection;

class ClassificationResponse extends SpatieData
{
    public function __construct(
        public string $id,
        public string $model,
        #[DataCollectionOf(ClassificationResult::class)]
        public DataCollection $results,
    ) {}
}
