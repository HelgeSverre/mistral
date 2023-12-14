<?php

namespace HelgeSverre\Mistral\Dto\Chat;

use HelgeSverre\Mistral\Dto\Usage;
use Spatie\LaravelData\Attributes\DataCollectionOf;
use Spatie\LaravelData\Data as SpatieData;
use Spatie\LaravelData\DataCollection;

class ChatCompletionResponse extends SpatieData
{
    public function __construct(
        public string $id,
        public string $object,
        public int $created,
        public string $model,
        #[DataCollectionOf(ChatCompletionChoice::class)]
        public DataCollection $choices,
        public Usage $usage,
    ) {
    }
}
