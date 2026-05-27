<?php

namespace HelgeSverre\Mistral\Dto\Audio;

use Spatie\LaravelData\Data;

class VoiceUpdateRequest extends Data
{
    /**
     * @param  string[]|null  $languages
     * @param  string[]|null  $tags
     */
    public function __construct(
        public ?string $name = null,
        public ?array $languages = null,
        public ?string $gender = null,
        public ?int $age = null,
        public ?array $tags = null,
    ) {}
}
