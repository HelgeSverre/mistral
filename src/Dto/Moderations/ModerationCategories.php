<?php

namespace HelgeSverre\Mistral\Dto\Moderations;

use Spatie\LaravelData\Attributes\MapName;
use Spatie\LaravelData\Data as SpatieData;

class ModerationCategories extends SpatieData
{
    public function __construct(
        public bool $sexual = false,
        #[MapName('hate_and_discrimination')]
        public bool $hateAndDiscrimination = false,
        #[MapName('violence_and_threats')]
        public bool $violenceAndThreats = false,
        #[MapName('dangerous_and_criminal_content')]
        public bool $dangerousAndCriminalContent = false,
        public bool $selfharm = false,
        public bool $health = false,
        public bool $financial = false,
        public bool $law = false,
        public bool $pii = false,
    ) {}
}
