<?php

namespace HelgeSverre\Mistral\Dto\Audio;

use Spatie\LaravelData\Attributes\MapName;
use Spatie\LaravelData\Data;

final class TranscriptionSegment extends Data
{
    public function __construct(
        public int $id,
        public int $seek,
        public float $start,
        public float $end,
        public string $text,
        /** @var array<int> */
        public array $tokens,
        public float $temperature,
        #[MapName('avg_logprob')]
        public float $avgLogprob,
        #[MapName('compression_ratio')]
        public float $compressionRatio,
        #[MapName('no_speech_prob')]
        public float $noSpeechProb,
    ) {}
}
