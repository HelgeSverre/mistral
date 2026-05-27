<?php

namespace HelgeSverre\Mistral\Dto\Audio;

use Spatie\LaravelData\Attributes\MapName;
use Spatie\LaravelData\Data;

class VoiceCreateRequest extends Data
{
    /**
     * @param  string  $sampleAudio  Base64-encoded audio file
     * @param  string[]  $languages
     * @param  string[]|null  $tags
     */
    public function __construct(
        public string $name,
        #[MapName('sample_audio')]
        public string $sampleAudio,
        public ?string $slug = null,
        public array $languages = [],
        public ?string $gender = null,
        public ?int $age = null,
        public ?array $tags = null,
        public ?string $color = null,
        #[MapName('retention_notice')]
        public int $retentionNotice = 30,
        #[MapName('sample_filename')]
        public ?string $sampleFilename = null,
    ) {}

    /**
     * Build a voice creation request from a local audio file (base64-encodes it for you).
     *
     * @param  string[]  $languages
     * @param  string[]|null  $tags
     */
    public static function fromFile(
        string $name,
        string $filePath,
        ?string $slug = null,
        array $languages = [],
        ?string $gender = null,
        ?int $age = null,
        ?array $tags = null,
        ?string $color = null,
        int $retentionNotice = 30,
    ): self {
        return new self(
            name: $name,
            sampleAudio: base64_encode((string) file_get_contents($filePath)),
            slug: $slug,
            languages: $languages,
            gender: $gender,
            age: $age,
            tags: $tags,
            color: $color,
            retentionNotice: $retentionNotice,
            sampleFilename: basename($filePath),
        );
    }
}
