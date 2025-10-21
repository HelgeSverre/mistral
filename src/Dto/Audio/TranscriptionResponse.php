<?php

namespace HelgeSverre\Mistral\Dto\Audio;

use Spatie\LaravelData\Attributes\DataCollectionOf;
use Spatie\LaravelData\Data;

final class TranscriptionResponse extends Data
{
    public function __construct(
        public ?string $text = null,
        public ?string $language = null,
        public ?float $duration = null,
        /** @var array<TranscriptionWord>|null */
        #[DataCollectionOf(TranscriptionWord::class)]
        public ?array $words = null,
        /** @var array<TranscriptionSegment>|null */
        #[DataCollectionOf(TranscriptionSegment::class)]
        public ?array $segments = null,
    ) {}

    /**
     * Get the full transcription text.
     * If the text field is null, reconstructs it from segments.
     */
    public function getText(): ?string
    {
        if ($this->text !== null) {
            return $this->text;
        }

        if ($this->segments === null || count($this->segments) === 0) {
            return null;
        }

        return implode(' ', array_map(fn (TranscriptionSegment $segment) => $segment->text, $this->segments));
    }
}
