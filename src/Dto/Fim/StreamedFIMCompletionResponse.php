<?php

namespace HelgeSverre\Mistral\Dto\Fim;

use Spatie\LaravelData\Attributes\DataCollectionOf;
use Spatie\LaravelData\Data as SpatieData;
use Spatie\LaravelData\DataCollection;

/**
 * @property-read StreamedFIMChoice[] $choices
 */
class StreamedFIMCompletionResponse extends SpatieData
{
    public function __construct(
        public string $id,
        public ?string $object,
        public ?int $created,
        public string $model,
        #[DataCollectionOf(StreamedFIMChoice::class)]
        public DataCollection $choices,
    ) {}
}
