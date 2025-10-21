<?php

namespace HelgeSverre\Mistral\Dto\FineTuning;

use Spatie\LaravelData\Attributes\MapName;
use Spatie\LaravelData\Data;

class TrainingParameters extends Data
{
    public function __construct(
        #[MapName('training_steps')]
        public ?int $trainingSteps = null,
        #[MapName('learning_rate')]
        public ?float $learningRate = null,
        #[MapName('weight_decay')]
        public ?float $weightDecay = null,
        #[MapName('warmup_fraction')]
        public ?float $warmupFraction = null,
        public ?int $epochs = null,
        #[MapName('fim_ratio')]
        public ?float $fimRatio = null,
        #[MapName('seq_len')]
        public ?int $seqLen = null,
    ) {}
}
