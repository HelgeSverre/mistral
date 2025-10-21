<?php

namespace HelgeSverre\Mistral\Dto\Audio;

use Spatie\LaravelData\Data;

final class TranscriptionWord extends Data
{
    public function __construct(
        public string $word,
        public float $start,
        public float $end,
    ) {}
}
