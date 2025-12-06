<?php

namespace HelgeSverre\Mistral\Dto\FineTuning;

use Spatie\LaravelData\Attributes\MapName;
use Spatie\LaravelData\Data;

class JobIn extends Data
{
    /**
     * @param  string  $model  The model to fine-tune (e.g. 'open-mistral-7b')
     * @param  TrainingFile[]  $trainingFiles  Training data files
     * @param  string[]|null  $validationFileIds  Validation file UUIDs
     * @param  WandbIntegration[]|null  $integrations  W&B integrations
     * @param  GithubRepositoryIn[]|null  $repositories  GitHub repositories for training
     * @param  ClassifierTargetIn[]|null  $classifierTargets  Classifier target definitions
     */
    public function __construct(
        public string $model,
        #[MapName('training_files')]
        public array $trainingFiles,
        #[MapName('validation_files')]
        public ?array $validationFileIds = null,
        public ?TrainingParameters $hyperparameters = null,
        public ?string $suffix = null,
        public ?array $integrations = null,
        #[MapName('auto_start')]
        public ?bool $autoStart = null,
        #[MapName('invalid_sample_skip_percentage')]
        public ?float $invalidSampleSkipPercentage = null,
        #[MapName('job_type')]
        public ?string $jobType = null,
        public ?array $repositories = null,
        #[MapName('classifier_targets')]
        public ?array $classifierTargets = null,
    ) {}
}
