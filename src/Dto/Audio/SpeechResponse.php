<?php

namespace HelgeSverre\Mistral\Dto\Audio;

use Spatie\LaravelData\Attributes\MapName;
use Spatie\LaravelData\Data;

class SpeechResponse extends Data
{
    public function __construct(
        #[MapName('audio_data')]
        public string $audioData,
    ) {}

    public function decoded(): string
    {
        return (string) base64_decode($this->audioData, true);
    }

    public function saveTo(string $filePath): int|false
    {
        return file_put_contents($filePath, $this->decoded());
    }
}
