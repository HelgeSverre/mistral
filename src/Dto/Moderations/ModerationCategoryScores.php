<?php

namespace HelgeSverre\Mistral\Dto\Moderations;

use Spatie\LaravelData\Attributes\MapName;
use Spatie\LaravelData\Data as SpatieData;

class ModerationCategoryScores extends SpatieData
{
    public function __construct(
        public float $sexual = 0.0,
        #[MapName('hate_and_discrimination')]
        public float $hateAndDiscrimination = 0.0,
        #[MapName('violence_and_threats')]
        public float $violenceAndThreats = 0.0,
        #[MapName('dangerous_and_criminal_content')]
        public float $dangerousAndCriminalContent = 0.0,
        public float $selfharm = 0.0,
        public float $health = 0.0,
        public float $financial = 0.0,
        public float $law = 0.0,
        public float $pii = 0.0,
    ) {}
}
