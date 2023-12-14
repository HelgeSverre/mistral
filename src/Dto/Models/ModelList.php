<?php

namespace HelgeSverre\Mistral\Dto\Models;

use Spatie\LaravelData\Attributes\DataCollectionOf;
use Spatie\LaravelData\Data as SpatieData;
use Spatie\LaravelData\DataCollection;

class ModelList extends SpatieData
{
    public function __construct(
        public ?string $object,
        #[DataCollectionOf(Model::class)]
        public ?DataCollection $data,
    ) {
    }
}
