<?php

namespace HelgeSverre\Mistral\Dto\Audio;

use Spatie\LaravelData\Attributes\DataCollectionOf;
use Spatie\LaravelData\Data;

final class TranscriptionResponse extends Data
{
    public function __construct(
        public string $text,
        public ?string $language = null,
        public ?float $duration = null,
        /** @var array<TranscriptionWord>|null */
        #[DataCollectionOf(TranscriptionWord::class)]
        public ?array $words = null,
        /** @var array<TranscriptionSegment>|null */
        #[DataCollectionOf(TranscriptionSegment::class)]
        public ?array $segments = null,
    ) {}
}
