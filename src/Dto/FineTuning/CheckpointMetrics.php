<?php

namespace HelgeSverre\Mistral\Dto\FineTuning;

use Spatie\LaravelData\Attributes\MapName;
use Spatie\LaravelData\Data;

class CheckpointMetrics extends Data
{
    public function __construct(
        #[MapName('train_loss')]
        public ?float $trainLoss = null,
        #[MapName('valid_loss')]
        public ?float $validLoss = null,
        #[MapName('valid_mean_token_accuracy')]
        public ?float $validMeanTokenAccuracy = null,
    ) {}
}
