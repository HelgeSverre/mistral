<?php

namespace HelgeSverre\Mistral\Dto\FineTuning;

use Spatie\LaravelData\Attributes\MapName;
use Spatie\LaravelData\Data;

class Checkpoint extends Data
{
    public function __construct(
        public CheckpointMetrics $metrics,
        #[MapName('step_number')]
        public int $stepNumber,
        #[MapName('created_at')]
        public int $createdAt,
    ) {}
}
