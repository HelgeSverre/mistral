<?php

/** @noinspection PhpUnhandledExceptionInspection */

use HelgeSverre\Mistral\Dto\Batch\BatchJobIn;
use HelgeSverre\Mistral\Dto\Batch\BatchJobOut;
use HelgeSverre\Mistral\Dto\Batch\BatchJobsOut;
use HelgeSverre\Mistral\Enums\BatchJobStatus;
use HelgeSverre\Mistral\Mistral;
use HelgeSverre\Mistral\Requests\Batch\CancelBatchJobRequest;
use HelgeSverre\Mistral\Requests\Batch\CreateBatchJobRequest;
use HelgeSverre\Mistral\Requests\Batch\GetBatchJobRequest;
use HelgeSverre\Mistral\Requests\Batch\ListBatchJobsRequest;
use Saloon\Http\Faking\MockResponse;
use Saloon\Laravel\Facades\Saloon;

beforeEach(function () {
    $this->mistral = new Mistral(apiKey: config('mistral.api_key'));
});

it('can list batch jobs', function () {
    Saloon::fake([
        ListBatchJobsRequest::class => MockResponse::fixture('batch/list'),
    ]);

    $response = $this->mistral->batch()->list();

    Saloon::assertSent(ListBatchJobsRequest::class);

    expect($response->status())->toBe(200);
});

it('can list batch jobs and convert to DTO', function () {
    Saloon::fake([
        ListBatchJobsRequest::class => MockResponse::fixture('batch/list'),
    ]);

    $batchJobsOut = $this->mistral->batch()->listAsDto();

    expect($batchJobsOut)
        ->toBeInstanceOf(BatchJobsOut::class)
        ->and($batchJobsOut->data)->toHaveCount(1)
        ->and($batchJobsOut->total)->toBe(1)
        ->and($batchJobsOut->object)->toBe('list')
        ->and($batchJobsOut->data[0])->toBeInstanceOf(BatchJobOut::class)
        ->and($batchJobsOut->data[0]->id)->toBe('877443e6-c853-4846-9857-6e4e0ab932f4')
        ->and($batchJobsOut->data[0]->status)->toBe(BatchJobStatus::RUNNING)
        ->and($batchJobsOut->data[0]->endpoint)->toBe('/v1/chat/completions')
        ->and($batchJobsOut->data[0]->model)->toBe('mistral-small-latest')
        ->and($batchJobsOut->data[0]->totalRequests)->toBe(3)
        ->and($batchJobsOut->data[0]->succeededRequests)->toBe(0)
        ->and($batchJobsOut->data[0]->failedRequests)->toBe(0);
});

it('can list batch jobs with query parameters', function () {
    Saloon::fake([
        ListBatchJobsRequest::class => MockResponse::fixture('batch/list'),
    ]);

    $response = $this->mistral->batch()->list(
        page: 1,
        pageSize: 10,
        model: 'mistral-large-latest',
        agentId: 'agent-123',
        metadata: ['experiment' => 'test-001'],
        createdAfter: '2024-05-15T00:00:00Z',
        createdByMe: true,
        status: ['RUNNING', 'SUCCESS']
    );

    Saloon::assertSent(function (ListBatchJobsRequest $request) {
        $query = $request->query()->all();

        return $query['page'] === 1
            && $query['page_size'] === 10
            && $query['model'] === 'mistral-large-latest'
            && $query['agent_id'] === 'agent-123'
            && isset($query['metadata'])
            && $query['created_after'] === '2024-05-15T00:00:00Z'
            && $query['created_by_me'] === true
            && isset($query['status']);
    });

    expect($response->status())->toBe(200);
});

it('can create a batch job', function () {
    Saloon::fake([
        CreateBatchJobRequest::class => MockResponse::fixture('batch/create'),
    ]);

    $batchJobIn = new BatchJobIn(
        inputFiles: ['92eceef6-84a2-4bc4-a064-ab6b6ec5b9a2'],
        endpoint: '/v1/chat/completions',
        model: 'mistral-small-latest'
    );

    $response = $this->mistral->batch()->create($batchJobIn);

    Saloon::assertSent(CreateBatchJobRequest::class);

    expect($response->status())->toBe(201);
});

it('can create batch job and convert to DTO', function () {
    Saloon::fake([
        CreateBatchJobRequest::class => MockResponse::fixture('batch/create'),
    ]);

    $batchJobIn = new BatchJobIn(
        inputFiles: ['92eceef6-84a2-4bc4-a064-ab6b6ec5b9a2'],
        endpoint: '/v1/chat/completions',
        model: 'mistral-small-latest'
    );

    $batchJobOut = $this->mistral->batch()->createAsDto($batchJobIn);

    expect($batchJobOut)
        ->toBeInstanceOf(BatchJobOut::class)
        ->and($batchJobOut->id)->toBe('877443e6-c853-4846-9857-6e4e0ab932f4')
        ->and($batchJobOut->status)->toBe(BatchJobStatus::QUEUED)
        ->and($batchJobOut->object)->toBe('batch')
        ->and($batchJobOut->inputFiles)->toBe(['92eceef6-84a2-4bc4-a064-ab6b6ec5b9a2'])
        ->and($batchJobOut->endpoint)->toBe('/v1/chat/completions')
        ->and($batchJobOut->model)->toBe('mistral-small-latest')
        ->and($batchJobOut->metadata)->toBeNull();
});

it('can create batch job with agent_id instead of model', function () {
    Saloon::fake([
        CreateBatchJobRequest::class => MockResponse::fixture('batch/create'),
    ]);

    $batchJobIn = new BatchJobIn(
        inputFiles: ['92eceef6-84a2-4bc4-a064-ab6b6ec5b9a2'],
        endpoint: '/v1/chat/completions',
        agentId: 'agent-abc123'
    );

    $response = $this->mistral->batch()->create($batchJobIn);

    Saloon::assertSent(function (CreateBatchJobRequest $request) {
        $body = $request->body()->all();

        return isset($body['agent_id'])
            && $body['agent_id'] === 'agent-abc123'
            && ! isset($body['model']);
    });

    expect($response->status())->toBe(201);
});

it('can get batch job details', function () {
    Saloon::fake([
        GetBatchJobRequest::class => MockResponse::fixture('batch/get'),
    ]);

    $response = $this->mistral->batch()->get('877443e6-c853-4846-9857-6e4e0ab932f4');

    Saloon::assertSent(GetBatchJobRequest::class);

    expect($response->status())->toBe(200);
});

it('can get batch job and convert to DTO', function () {
    Saloon::fake([
        GetBatchJobRequest::class => MockResponse::fixture('batch/get'),
    ]);

    $batchJobOut = $this->mistral->batch()->getAsDto('877443e6-c853-4846-9857-6e4e0ab932f4');

    expect($batchJobOut)
        ->toBeInstanceOf(BatchJobOut::class)
        ->and($batchJobOut->id)->toBe('877443e6-c853-4846-9857-6e4e0ab932f4')
        ->and($batchJobOut->status)->toBe(BatchJobStatus::RUNNING)
        ->and($batchJobOut->totalRequests)->toBe(3)
        ->and($batchJobOut->succeededRequests)->toBe(0)
        ->and($batchJobOut->failedRequests)->toBe(0)
        ->and($batchJobOut->outputFile)->toBeNull()
        ->and($batchJobOut->errorFile)->toBeNull()
        ->and($batchJobOut->startedAt)->toBe(1761710171)
        ->and($batchJobOut->completedAt)->toBeNull();
});

it('can cancel a batch job', function () {
    Saloon::fake([
        CancelBatchJobRequest::class => MockResponse::fixture('batch/cancel'),
    ]);

    $response = $this->mistral->batch()->cancel('877443e6-c853-4846-9857-6e4e0ab932f4');

    Saloon::assertSent(CancelBatchJobRequest::class);

    expect($response->status())->toBe(200);
});

it('can cancel batch job and convert to DTO', function () {
    Saloon::fake([
        CancelBatchJobRequest::class => MockResponse::fixture('batch/cancel'),
    ]);

    $batchJobOut = $this->mistral->batch()->cancelAsDto('877443e6-c853-4846-9857-6e4e0ab932f4');

    expect($batchJobOut)
        ->toBeInstanceOf(BatchJobOut::class)
        ->and($batchJobOut->id)->toBe('877443e6-c853-4846-9857-6e4e0ab932f4')
        ->and($batchJobOut->status)->toBe(BatchJobStatus::SUCCESS)
        ->and($batchJobOut->totalRequests)->toBe(3)
        ->and($batchJobOut->succeededRequests)->toBe(3)
        ->and($batchJobOut->outputFile)->toBe('57398329-049b-4556-9b64-3691090365ca');
});

it('BatchJobStatus enum has all required values', function () {
    $statuses = [
        BatchJobStatus::QUEUED,
        BatchJobStatus::RUNNING,
        BatchJobStatus::SUCCESS,
        BatchJobStatus::FAILED,
        BatchJobStatus::CANCELLED,
        BatchJobStatus::CANCELLATION_REQUESTED,
    ];

    expect($statuses)->toHaveCount(6)
        ->and(BatchJobStatus::QUEUED->value)->toBe('QUEUED')
        ->and(BatchJobStatus::RUNNING->value)->toBe('RUNNING')
        ->and(BatchJobStatus::SUCCESS->value)->toBe('SUCCESS')
        ->and(BatchJobStatus::FAILED->value)->toBe('FAILED')
        ->and(BatchJobStatus::CANCELLED->value)->toBe('CANCELLED')
        ->and(BatchJobStatus::CANCELLATION_REQUESTED->value)->toBe('CANCELLATION_REQUESTED');
});

it('BatchJobIn DTO correctly maps snake_case', function () {
    $batchJobIn = new BatchJobIn(
        inputFiles: ['file-001', 'file-002'],
        endpoint: '/v1/chat/completions',
        model: 'mistral-large-latest',
        agentId: 'agent-123',
        metadata: ['key' => 'value'],
        timeoutHours: 48
    );

    $array = $batchJobIn->toArray();

    expect($array)
        ->toHaveKey('input_files')
        ->toHaveKey('endpoint')
        ->toHaveKey('model')
        ->toHaveKey('agent_id')
        ->toHaveKey('metadata')
        ->toHaveKey('timeout_hours')
        ->and($array['input_files'])->toBe(['file-001', 'file-002'])
        ->and($array['endpoint'])->toBe('/v1/chat/completions')
        ->and($array['model'])->toBe('mistral-large-latest')
        ->and($array['agent_id'])->toBe('agent-123')
        ->and($array['timeout_hours'])->toBe(48);
});

it('BatchJobOut DTO correctly handles nullable fields', function () {
    $data = [
        'id' => 'batch-job-123',
        'object' => 'batch.job',
        'status' => 'QUEUED',
        'input_files' => ['file-001'],
        'endpoint' => '/v1/chat/completions',
        'created_at' => 1715693000,
    ];

    $batchJobOut = BatchJobOut::from($data);

    expect($batchJobOut->id)->toBe('batch-job-123')
        ->and($batchJobOut->status)->toBe(BatchJobStatus::QUEUED)
        ->and($batchJobOut->startedAt)->toBeNull()
        ->and($batchJobOut->completedAt)->toBeNull()
        ->and($batchJobOut->model)->toBeNull()
        ->and($batchJobOut->agentId)->toBeNull()
        ->and($batchJobOut->metadata)->toBeNull()
        ->and($batchJobOut->outputFile)->toBeNull()
        ->and($batchJobOut->errorFile)->toBeNull()
        ->and($batchJobOut->totalRequests)->toBeNull()
        ->and($batchJobOut->succeededRequests)->toBeNull()
        ->and($batchJobOut->failedRequests)->toBeNull();
});

it('can handle metadata as array in BatchJobIn', function () {
    $batchJobIn = new BatchJobIn(
        inputFiles: ['file-001'],
        endpoint: '/v1/chat/completions',
        model: 'mistral-large-latest',
        metadata: [
            'experiment' => 'exp-001',
            'version' => '1.0',
            'tags' => ['test', 'batch'],
        ]
    );

    $array = $batchJobIn->toArray();

    expect($array['metadata'])
        ->toBeArray()
        ->and($array['metadata']['experiment'])->toBe('exp-001')
        ->and($array['metadata']['version'])->toBe('1.0')
        ->and($array['metadata']['tags'])->toBe(['test', 'batch']);
});
