<?php

namespace HelgeSverre\Mistral\Dto\Audio;

use Spatie\LaravelData\Attributes\MapName;
use Spatie\LaravelData\Data;

class SpeechStreamAudioDelta extends Data
{
    public function __construct(
        #[MapName('audio_data')]
        public string $audioData,
        public string $type = 'speech.audio.delta',
    ) {}

    public function decoded(): string
    {
        return (string) base64_decode($this->audioData, true);
    }
}
