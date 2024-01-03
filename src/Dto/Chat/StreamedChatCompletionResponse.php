<?php

namespace HelgeSverre\Mistral\Dto\Chat;

use Spatie\LaravelData\Attributes\DataCollectionOf;
use Spatie\LaravelData\Data as SpatieData;
use Spatie\LaravelData\DataCollection;

/**
 * @property-read StreamedChatCompletionChoice[] $choices
 */
class StreamedChatCompletionResponse extends SpatieData
{
    public function __construct(
        public string $id,
        public ?string $object,
        public ?int $created,
        public string $model,
        #[DataCollectionOf(StreamedChatCompletionChoice::class)]
        public DataCollection $choices,
    ) {
    }
}
