<?php

namespace HelgeSverre\Mistral\Dto\Moderations;

use Spatie\LaravelData\Attributes\DataCollectionOf;
use Spatie\LaravelData\Data as SpatieData;
use Spatie\LaravelData\DataCollection;

class ModerationResponse extends SpatieData
{
    public function __construct(
        public string $id,
        public string $model,
        #[DataCollectionOf(ModerationResult::class)]
        public DataCollection $results,
    ) {}
}
