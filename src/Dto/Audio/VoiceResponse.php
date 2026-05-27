<?php

namespace HelgeSverre\Mistral\Dto\Audio;

use Spatie\LaravelData\Attributes\MapName;
use Spatie\LaravelData\Data;

class VoiceResponse extends Data
{
    /**
     * @param  string[]  $languages
     * @param  string[]|null  $tags
     */
    public function __construct(
        public string $id,
        public string $name,
        #[MapName('created_at')]
        public string $createdAt,
        public ?string $slug = null,
        public array $languages = [],
        public ?string $gender = null,
        public ?int $age = null,
        public ?array $tags = null,
        public ?string $color = null,
        #[MapName('retention_notice')]
        public int $retentionNotice = 30,
        #[MapName('user_id')]
        public ?string $userId = null,
    ) {}
}
