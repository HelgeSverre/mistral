<?php

namespace HelgeSverre\Mistral\Dto\Models;

use Spatie\LaravelData\Attributes\MapName;
use Spatie\LaravelData\Data as SpatieData;

/**
 * Model Capabilities
 */
class ModelCapabilities extends SpatieData
{
    public function __construct(
        #[MapName('completion_chat')]
        public bool $completionChat = true,

        #[MapName('completion_fim')]
        public bool $completionFim = false,

        #[MapName('function_calling')]
        public bool $functionCalling = true,

        #[MapName('fine_tuning')]
        public bool $fineTuning = false,

        public bool $vision = false,

        public bool $classification = false,
    ) {}
}
