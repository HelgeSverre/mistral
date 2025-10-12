<?php

namespace HelgeSverre\Mistral\Dto\Moderations;

use Spatie\LaravelData\Attributes\MapName;
use Spatie\LaravelData\Data as SpatieData;

class ModerationCategoryScores extends SpatieData
{
    public function __construct(
        public float $sexual = 0.0,
        public float $hate = 0.0,
        public float $violence = 0.0,
        #[MapName('self-harm')]
        public float $selfHarm = 0.0,
        #[MapName('sexual/minors')]
        public float $sexualMinors = 0.0,
        #[MapName('hate/threatening')]
        public float $hateThreatening = 0.0,
        #[MapName('violence/graphic')]
        public float $violenceGraphic = 0.0,
    ) {}
}
