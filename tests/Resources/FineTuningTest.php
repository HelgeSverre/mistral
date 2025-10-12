<?php

/** @noinspection PhpUnhandledExceptionInspection */

use HelgeSverre\Mistral\Dto\FineTuning\ArchiveFTModelOut;
use HelgeSverre\Mistral\Dto\FineTuning\ClassifierJobOut;
use HelgeSverre\Mistral\Dto\FineTuning\CompletionDetailedJobOut;
use HelgeSverre\Mistral\Dto\FineTuning\CompletionFTModelOut;
use HelgeSverre\Mistral\Dto\FineTuning\CompletionJobOut;
use HelgeSverre\Mistral\Dto\FineTuning\JobIn;
use HelgeSverre\Mistral\Dto\FineTuning\JobsOut;
use HelgeSverre\Mistral\Dto\FineTuning\LegacyJobMetadataOut;
use HelgeSverre\Mistral\Dto\FineTuning\TrainingFile;
use HelgeSverre\Mistral\Dto\FineTuning\TrainingParameters;
use HelgeSverre\Mistral\Dto\FineTuning\UnarchiveFTModelOut;
use HelgeSverre\Mistral\Dto\FineTuning\UpdateFTModelIn;
use HelgeSverre\Mistral\Dto\FineTuning\WandbIntegration;
use HelgeSverre\Mistral\Enums\JobStatus;
use HelgeSverre\Mistral\Requests\FineTuning\ArchiveModelRequest;
use HelgeSverre\Mistral\Requests\FineTuning\CancelJobRequest;
use HelgeSverre\Mistral\Requests\FineTuning\CreateJobRequest;
use HelgeSverre\Mistral\Requests\FineTuning\GetJobRequest;
use HelgeSverre\Mistral\Requests\FineTuning\ListJobsRequest;
use HelgeSverre\Mistral\Requests\FineTuning\StartJobRequest;
use HelgeSverre\Mistral\Requests\FineTuning\UnarchiveModelRequest;
use HelgeSverre\Mistral\Requests\FineTuning\UpdateModelRequest;
use Saloon\Http\Faking\MockResponse;
use Saloon\Laravel\Facades\Saloon;

beforeEach(function () {
    $this->mistral = new HelgeSverre\Mistral\Mistral(apiKey: config('mistral.api_key'));
});

it('can list fine-tuning jobs', function () {
    Saloon::fake([
        ListJobsRequest::class => MockResponse::fixture('fine_tuning.listJobs'),
    ]);

    $response = $this->mistral->fineTuning()->list();

    Saloon::assertSent(ListJobsRequest::class);

    expect($response->status())->toBe(200);
});

it('can list jobs and convert to DTO', function () {
    Saloon::fake([
        ListJobsRequest::class => MockResponse::fixture('fine_tuning.listJobs'),
    ]);

    $jobsOut = $this->mistral->fineTuning()->listAsDto();

    expect($jobsOut)
        ->toBeInstanceOf(JobsOut::class)
        ->and($jobsOut->data)->toHaveCount(2)
        ->and($jobsOut->total)->toBe(2)
        ->and($jobsOut->data[0])->toBeInstanceOf(CompletionJobOut::class)
        ->and($jobsOut->data[0]->id)->toBe('f1a2b3c4-d5e6-7890-abcd-ef1234567890')
        ->and($jobsOut->data[0]->status)->toBe(JobStatus::SUCCESS)
        ->and($jobsOut->data[0]->jobType)->toBe('completion')
        ->and($jobsOut->data[0]->fineTunedModel)->toBe('ft:open-mistral-7b:587a6b29:20240514:7e773925')
        ->and($jobsOut->data[1])->toBeInstanceOf(ClassifierJobOut::class)
        ->and($jobsOut->data[1]->status)->toBe(JobStatus::RUNNING);
});

it('can list jobs with query parameters', function () {
    Saloon::fake([
        ListJobsRequest::class => MockResponse::fixture('fine_tuning.listJobs'),
    ]);

    $response = $this->mistral->fineTuning()->list(
        page: 1,
        pageSize: 10,
        model: 'open-mistral-7b',
        status: 'SUCCESS',
        createdByMe: true,
        suffix: 'my-model'
    );

    Saloon::assertSent(function (ListJobsRequest $request) {
        $query = $request->query()->all();

        return $query['page'] === 1
            && $query['page_size'] === 10
            && $query['model'] === 'open-mistral-7b'
            && $query['status'] === 'SUCCESS'
            && $query['created_by_me'] === true
            && $query['suffix'] === 'my-model';
    });

    expect($response->status())->toBe(200);
});

it('can create a fine-tuning job', function () {
    Saloon::fake([
        CreateJobRequest::class => MockResponse::fixture('fine_tuning.createJobCompletion'),
    ]);

    $jobIn = new JobIn(
        model: 'open-mistral-7b',
        trainingFiles: [
            new TrainingFile(fileId: '9876dcba-4321-0987-fedc-ba9876543210', weight: 1),
        ],
        hyperparameters: new TrainingParameters(
            trainingSteps: 100,
            learningRate: 0.0001,
            weightDecay: 0.1,
            warmupFraction: 0.05,
            epochs: 3,
            fimRatio: 0.9,
            seqLen: 512,
        ),
        suffix: 'my-model-v1',
        integrations: new WandbIntegration(
            project: 'my-wandb-project',
            name: 'my-run-name',
        ),
        autoStart: false,
    );

    $response = $this->mistral->fineTuning()->create($jobIn);

    Saloon::assertSent(CreateJobRequest::class);

    expect($response->status())->toBe(200);
});

it('can create job and convert to DTO for completion type', function () {
    Saloon::fake([
        CreateJobRequest::class => MockResponse::fixture('fine_tuning.createJobCompletion'),
    ]);

    $jobIn = new JobIn(
        model: 'open-mistral-7b',
        trainingFiles: [
            new TrainingFile(fileId: '9876dcba-4321-0987-fedc-ba9876543210'),
        ],
    );

    $jobOut = $this->mistral->fineTuning()->createAsDto($jobIn);

    expect($jobOut)
        ->toBeInstanceOf(CompletionJobOut::class)
        ->and($jobOut->id)->toBe('f1a2b3c4-d5e6-7890-abcd-ef1234567890')
        ->and($jobOut->status)->toBe(JobStatus::QUEUED)
        ->and($jobOut->jobType)->toBe('completion')
        ->and($jobOut->model)->toBe('open-mistral-7b')
        ->and($jobOut->suffix)->toBe('my-model-v1')
        ->and($jobOut->integrations)->toBeInstanceOf(WandbIntegration::class)
        ->and($jobOut->integrations->project)->toBe('my-wandb-project');
});

it('can create job with dry run', function () {
    Saloon::fake([
        CreateJobRequest::class => MockResponse::fixture('fine_tuning.createJobDryRun'),
    ]);

    $jobIn = new JobIn(
        model: 'open-mistral-7b',
        trainingFiles: [
            new TrainingFile(fileId: '9876dcba-4321-0987-fedc-ba9876543210'),
        ],
    );

    $metadataOut = $this->mistral->fineTuning()->createAsDto($jobIn, dryRun: true);

    expect($metadataOut)
        ->toBeInstanceOf(LegacyJobMetadataOut::class)
        ->and($metadataOut->expectedDurationSeconds)->toBe(300)
        ->and($metadataOut->cost)->toBe(5.99)
        ->and($metadataOut->costCurrency)->toBe('USD');

    Saloon::assertSent(function (CreateJobRequest $request) {
        $query = $request->query()->all();

        return isset($query['dry_run']) && $query['dry_run'] === true;
    });
});

it('can get detailed job information', function () {
    Saloon::fake([
        GetJobRequest::class => MockResponse::fixture('fine_tuning.getJobDetailed'),
    ]);

    $response = $this->mistral->fineTuning()->get('f1a2b3c4-d5e6-7890-abcd-ef1234567890');

    Saloon::assertSent(GetJobRequest::class);

    expect($response->status())->toBe(200);
});

it('can get job and convert to detailed DTO', function () {
    Saloon::fake([
        GetJobRequest::class => MockResponse::fixture('fine_tuning.getJobDetailed'),
    ]);

    $jobOut = $this->mistral->fineTuning()->getAsDto('f1a2b3c4-d5e6-7890-abcd-ef1234567890');

    expect($jobOut)
        ->toBeInstanceOf(CompletionDetailedJobOut::class)
        ->and($jobOut->id)->toBe('f1a2b3c4-d5e6-7890-abcd-ef1234567890')
        ->and($jobOut->status)->toBe(JobStatus::SUCCESS)
        ->and($jobOut->fineTunedModel)->toBe('ft:open-mistral-7b:587a6b29:20240514:7e773925')
        ->and($jobOut->trainedTokens)->toBe(458752)
        ->and($jobOut->checkpoints)->toHaveCount(2)
        ->and($jobOut->checkpoints[0]->stepNumber)->toBe(50)
        ->and($jobOut->checkpoints[0]->metrics->trainLoss)->toBe(0.245)
        ->and($jobOut->checkpoints[1]->stepNumber)->toBe(100)
        ->and($jobOut->events)->toBeArray();
});

it('can cancel a fine-tuning job', function () {
    Saloon::fake([
        CancelJobRequest::class => MockResponse::fixture('fine_tuning.cancelJob'),
    ]);

    $response = $this->mistral->fineTuning()->cancel('f1a2b3c4-d5e6-7890-abcd-ef1234567890');

    Saloon::assertSent(CancelJobRequest::class);

    expect($response->status())->toBe(200);
});

it('can cancel job and convert to DTO', function () {
    Saloon::fake([
        CancelJobRequest::class => MockResponse::fixture('fine_tuning.cancelJob'),
    ]);

    $jobOut = $this->mistral->fineTuning()->cancelAsDto('f1a2b3c4-d5e6-7890-abcd-ef1234567890');

    expect($jobOut)
        ->toBeInstanceOf(CompletionDetailedJobOut::class)
        ->and($jobOut->status)->toBe(JobStatus::CANCELLATION_REQUESTED);
});

it('can start a validated fine-tuning job', function () {
    Saloon::fake([
        StartJobRequest::class => MockResponse::fixture('fine_tuning.startJob'),
    ]);

    $response = $this->mistral->fineTuning()->start('f1a2b3c4-d5e6-7890-abcd-ef1234567890');

    Saloon::assertSent(StartJobRequest::class);

    expect($response->status())->toBe(200);
});

it('can start job and convert to DTO', function () {
    Saloon::fake([
        StartJobRequest::class => MockResponse::fixture('fine_tuning.startJob'),
    ]);

    $jobOut = $this->mistral->fineTuning()->startAsDto('f1a2b3c4-d5e6-7890-abcd-ef1234567890');

    expect($jobOut)
        ->toBeInstanceOf(CompletionDetailedJobOut::class)
        ->and($jobOut->status)->toBe(JobStatus::STARTED);
});

it('can update fine-tuned model metadata', function () {
    Saloon::fake([
        UpdateModelRequest::class => MockResponse::fixture('fine_tuning.updateModel'),
    ]);

    $updateModel = new UpdateFTModelIn(
        name: 'My Updated Model Name',
        description: 'This is my updated model description',
    );

    $response = $this->mistral->fineTuning()->updateModel(
        'ft:open-mistral-7b:587a6b29:20240514:7e773925',
        $updateModel
    );

    Saloon::assertSent(UpdateModelRequest::class);

    expect($response->status())->toBe(200);
});

it('can update model and convert to DTO', function () {
    Saloon::fake([
        UpdateModelRequest::class => MockResponse::fixture('fine_tuning.updateModel'),
    ]);

    $updateModel = new UpdateFTModelIn(
        name: 'My Updated Model Name',
        description: 'This is my updated model description',
    );

    $modelOut = $this->mistral->fineTuning()->updateModelAsDto(
        'ft:open-mistral-7b:587a6b29:20240514:7e773925',
        $updateModel
    );

    expect($modelOut)
        ->toBeInstanceOf(CompletionFTModelOut::class)
        ->and($modelOut->id)->toBe('ft:open-mistral-7b:587a6b29:20240514:7e773925')
        ->and($modelOut->name)->toBe('My Updated Model Name')
        ->and($modelOut->description)->toBe('This is my updated model description')
        ->and($modelOut->modelType)->toBe('completion')
        ->and($modelOut->archived)->toBeFalse();
});

it('can archive a fine-tuned model', function () {
    Saloon::fake([
        ArchiveModelRequest::class => MockResponse::fixture('fine_tuning.archiveModel'),
    ]);

    $response = $this->mistral->fineTuning()->archiveModel('ft:open-mistral-7b:587a6b29:20240514:7e773925');

    Saloon::assertSent(ArchiveModelRequest::class);

    expect($response->status())->toBe(200);
});

it('can archive model and convert to DTO', function () {
    Saloon::fake([
        ArchiveModelRequest::class => MockResponse::fixture('fine_tuning.archiveModel'),
    ]);

    $archiveOut = $this->mistral->fineTuning()->archiveModelAsDto('ft:open-mistral-7b:587a6b29:20240514:7e773925');

    expect($archiveOut)
        ->toBeInstanceOf(ArchiveFTModelOut::class)
        ->and($archiveOut->id)->toBe('ft:open-mistral-7b:587a6b29:20240514:7e773925')
        ->and($archiveOut->archived)->toBeTrue()
        ->and($archiveOut->modelType)->toBe('completion');
});

it('can unarchive a fine-tuned model', function () {
    Saloon::fake([
        UnarchiveModelRequest::class => MockResponse::fixture('fine_tuning.unarchiveModel'),
    ]);

    $response = $this->mistral->fineTuning()->unarchiveModel('ft:open-mistral-7b:587a6b29:20240514:7e773925');

    Saloon::assertSent(UnarchiveModelRequest::class);

    expect($response->status())->toBe(200);
});

it('can unarchive model and convert to DTO', function () {
    Saloon::fake([
        UnarchiveModelRequest::class => MockResponse::fixture('fine_tuning.unarchiveModel'),
    ]);

    $unarchiveOut = $this->mistral->fineTuning()->unarchiveModelAsDto('ft:open-mistral-7b:587a6b29:20240514:7e773925');

    expect($unarchiveOut)
        ->toBeInstanceOf(UnarchiveFTModelOut::class)
        ->and($unarchiveOut->id)->toBe('ft:open-mistral-7b:587a6b29:20240514:7e773925')
        ->and($unarchiveOut->archived)->toBeFalse()
        ->and($unarchiveOut->modelType)->toBe('completion');
});

it('JobStatus enum has all required values', function () {
    $statuses = [
        JobStatus::QUEUED,
        JobStatus::STARTED,
        JobStatus::VALIDATING,
        JobStatus::VALIDATED,
        JobStatus::RUNNING,
        JobStatus::FAILED_VALIDATION,
        JobStatus::FAILED,
        JobStatus::SUCCESS,
        JobStatus::CANCELLED,
        JobStatus::CANCELLATION_REQUESTED,
    ];

    expect($statuses)->toHaveCount(10)
        ->and(JobStatus::QUEUED->value)->toBe('QUEUED')
        ->and(JobStatus::SUCCESS->value)->toBe('SUCCESS')
        ->and(JobStatus::CANCELLED->value)->toBe('CANCELLED');
});

it('TrainingFile DTO works correctly', function () {
    $trainingFile = new TrainingFile(
        fileId: '9876dcba-4321-0987-fedc-ba9876543210',
        weight: 1
    );

    $array = $trainingFile->toArray();

    expect($array)
        ->toHaveKey('file_id')
        ->and($array['file_id'])->toBe('9876dcba-4321-0987-fedc-ba9876543210')
        ->and($array['weight'])->toBe(1);
});

it('TrainingParameters DTO correctly maps snake_case', function () {
    $params = new TrainingParameters(
        trainingSteps: 100,
        learningRate: 0.0001,
        weightDecay: 0.1,
        warmupFraction: 0.05,
        epochs: 3,
        fimRatio: 0.9,
        seqLen: 512,
    );

    $array = $params->toArray();

    expect($array)
        ->toHaveKey('training_steps')
        ->toHaveKey('learning_rate')
        ->toHaveKey('weight_decay')
        ->toHaveKey('warmup_fraction')
        ->toHaveKey('fim_ratio')
        ->toHaveKey('seq_len')
        ->and($array['training_steps'])->toBe(100)
        ->and($array['learning_rate'])->toBe(0.0001);
});

it('WandbIntegration DTO works correctly', function () {
    $wandb = new WandbIntegration(
        project: 'my-project',
        name: 'my-run',
        key: 'secret-key'
    );

    $array = $wandb->toArray();

    expect($array)
        ->toHaveKey('project')
        ->and($array['project'])->toBe('my-project')
        ->and($array['name'])->toBe('my-run')
        ->and($array['key'])->toBe('secret-key');
});
