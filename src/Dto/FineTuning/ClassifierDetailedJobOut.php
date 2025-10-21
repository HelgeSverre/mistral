<?php

namespace HelgeSverre\Mistral\Dto\FineTuning;

use HelgeSverre\Mistral\Enums\JobStatus;
use Spatie\LaravelData\Attributes\MapName;
use Spatie\LaravelData\Data;

class ClassifierDetailedJobOut extends Data
{
    /**
     * @param  TrainingFile[]  $trainingFiles
     * @param  TrainingFile[]|null  $validationFiles
     * @param  Checkpoint[]|null  $checkpoints
     * @param  ValidationError[]|null  $events
     */
    public function __construct(
        public string $id,
        #[MapName('auto_start')]
        public bool $autoStart,
        public TrainingParameters $hyperparameters,
        public string $model,
        public JobStatus $status,
        #[MapName('job_type')]
        public string $jobType,
        #[MapName('created_at')]
        public int $createdAt,
        #[MapName('modified_at')]
        public int $modifiedAt,
        #[MapName('training_files')]
        public array $trainingFiles,
        #[MapName('validation_files')]
        public ?array $validationFiles = null,
        public string $object = 'job',
        #[MapName('fine_tuned_model')]
        public ?string $fineTunedModel = null,
        public ?string $suffix = null,
        public ?WandbIntegration $integrations = null,
        #[MapName('trained_tokens')]
        public ?int $trainedTokens = null,
        public ?JobMetadata $metadata = null,
        public ?array $checkpoints = null,
        public ?array $events = null,
    ) {}
}
