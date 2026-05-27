<?php

namespace HelgeSverre\Mistral\Dto\Audio;

use HelgeSverre\Mistral\Dto\Usage;
use Spatie\LaravelData\Data;

class SpeechStreamDone extends Data
{
    public function __construct(
        public Usage $usage,
        public string $type = 'speech.audio.done',
    ) {}
}
