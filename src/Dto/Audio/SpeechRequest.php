<?php

namespace HelgeSverre\Mistral\Dto\Audio;

use HelgeSverre\Mistral\Enums\SpeechOutputFormat;
use Spatie\LaravelData\Attributes\MapName;
use Spatie\LaravelData\Data;

class SpeechRequest extends Data
{
    public function __construct(
        public string $input,
        public ?string $model = null,
        #[MapName('voice_id')]
        public ?string $voiceId = null,
        #[MapName('ref_audio')]
        public ?string $refAudio = null,
        #[MapName('response_format')]
        public ?SpeechOutputFormat $responseFormat = null,
        public bool $stream = false,
    ) {}

    public static function withVoice(
        string $input,
        string $voiceId,
        ?string $model = null,
        ?SpeechOutputFormat $responseFormat = null,
    ): self {
        return new self(
            input: $input,
            model: $model,
            voiceId: $voiceId,
            responseFormat: $responseFormat,
        );
    }

    public static function withRefAudio(
        string $input,
        string $refAudioBase64,
        ?string $model = null,
        ?SpeechOutputFormat $responseFormat = null,
    ): self {
        return new self(
            input: $input,
            model: $model,
            refAudio: $refAudioBase64,
            responseFormat: $responseFormat,
        );
    }

    public static function withRefAudioFile(
        string $input,
        string $filePath,
        ?string $model = null,
        ?SpeechOutputFormat $responseFormat = null,
    ): self {
        return self::withRefAudio(
            input: $input,
            refAudioBase64: base64_encode((string) file_get_contents($filePath)),
            model: $model,
            responseFormat: $responseFormat,
        );
    }
}
