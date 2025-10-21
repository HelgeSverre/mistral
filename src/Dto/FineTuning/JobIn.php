<?php

namespace HelgeSverre\Mistral\Dto\FineTuning;

use Spatie\LaravelData\Attributes\MapName;
use Spatie\LaravelData\Data;

class JobIn extends Data
{
    /**
     * @param  TrainingFile[]  $trainingFiles
     * @param  TrainingFile[]|null  $validationFiles
     */
    public function __construct(
        public string $model,
        #[MapName('training_files')]
        public array $trainingFiles,
        #[MapName('validation_files')]
        public ?array $validationFiles = null,
        public ?TrainingParameters $hyperparameters = null,
        public ?string $suffix = null,
        public ?WandbIntegration $integrations = null,
        #[MapName('auto_start')]
        public ?bool $autoStart = null,
    ) {}
}
