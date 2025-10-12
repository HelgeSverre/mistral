<?php

namespace HelgeSverre\Mistral\Dto\Moderations;

use Spatie\LaravelData\Attributes\MapName;
use Spatie\LaravelData\Data as SpatieData;

class ModerationCategories extends SpatieData
{
    public function __construct(
        public bool $sexual = false,
        public bool $hate = false,
        public bool $violence = false,
        #[MapName('self-harm')]
        public bool $selfHarm = false,
        #[MapName('sexual/minors')]
        public bool $sexualMinors = false,
        #[MapName('hate/threatening')]
        public bool $hateThreatening = false,
        #[MapName('violence/graphic')]
        public bool $violenceGraphic = false,
    ) {}
}
