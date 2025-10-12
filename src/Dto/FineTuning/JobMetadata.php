<?php

namespace HelgeSverre\Mistral\Dto\FineTuning;

use Spatie\LaravelData\Attributes\MapName;
use Spatie\LaravelData\Data;

class JobMetadata extends Data
{
    public function __construct(
        #[MapName('expected_duration_seconds')]
        public ?int $expectedDurationSeconds = null,
        #[MapName('cost')]
        public ?float $cost = null,
        #[MapName('cost_currency')]
        public ?string $costCurrency = null,
        #[MapName('train_tokens_per_step')]
        public ?int $trainTokensPerStep = null,
        #[MapName('train_tokens')]
        public ?int $trainTokens = null,
        #[MapName('data_tokens')]
        public ?int $dataTokens = null,
        #[MapName('estimated_start_time')]
        public ?int $estimatedStartTime = null,
    ) {}
}
