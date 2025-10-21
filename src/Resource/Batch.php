<?php

namespace HelgeSverre\Mistral\Resource;

use HelgeSverre\Mistral\Dto\Batch\BatchJobIn;
use HelgeSverre\Mistral\Dto\Batch\BatchJobOut;
use HelgeSverre\Mistral\Dto\Batch\BatchJobsOut;
use HelgeSverre\Mistral\Requests\Batch\CancelBatchJobRequest;
use HelgeSverre\Mistral\Requests\Batch\CreateBatchJobRequest;
use HelgeSverre\Mistral\Requests\Batch\GetBatchJobRequest;
use HelgeSverre\Mistral\Requests\Batch\ListBatchJobsRequest;
use Saloon\Http\BaseResource;
use Saloon\Http\Response;

class Batch extends BaseResource
{
    /**
     * List batch jobs with optional filters
     *
     * @param  array<string>|null  $status  Array of BatchJobStatus values
     */
    public function list(
        ?int $page = null,
        ?int $pageSize = null,
        ?string $model = null,
        ?string $agentId = null,
        ?array $metadata = null,
        ?string $createdAfter = null,
        ?bool $createdByMe = null,
        ?array $status = null,
    ): Response {
        return $this->connector->send(new ListBatchJobsRequest(
            page: $page,
            pageSize: $pageSize,
            model: $model,
            agentId: $agentId,
            metadata: $metadata,
            createdAfter: $createdAfter,
            createdByMe: $createdByMe,
            status: $status,
        ));
    }

    /**
     * Create a new batch job
     */
    public function create(BatchJobIn $batchJobIn): Response
    {
        return $this->connector->send(new CreateBatchJobRequest($batchJobIn));
    }

    /**
     * Get detailed information about a batch job
     *
     * @param  string  $jobId  The UUID of the batch job
     */
    public function get(string $jobId): Response
    {
        return $this->connector->send(new GetBatchJobRequest($jobId));
    }

    /**
     * Cancel a running batch job
     *
     * @param  string  $jobId  The UUID of the batch job
     */
    public function cancel(string $jobId): Response
    {
        return $this->connector->send(new CancelBatchJobRequest($jobId));
    }

    /**
     * Helper method to convert list response to DTO
     *
     * @param  array<string>|null  $status  Array of BatchJobStatus values
     */
    public function listAsDto(
        ?int $page = null,
        ?int $pageSize = null,
        ?string $model = null,
        ?string $agentId = null,
        ?array $metadata = null,
        ?string $createdAfter = null,
        ?bool $createdByMe = null,
        ?array $status = null,
    ): BatchJobsOut {
        $response = $this->list(
            page: $page,
            pageSize: $pageSize,
            model: $model,
            agentId: $agentId,
            metadata: $metadata,
            createdAfter: $createdAfter,
            createdByMe: $createdByMe,
            status: $status,
        );

        return BatchJobsOut::from($response->json());
    }

    /**
     * Helper method to convert create response to DTO
     */
    public function createAsDto(BatchJobIn $batchJobIn): BatchJobOut
    {
        $response = $this->create($batchJobIn);

        return BatchJobOut::from($response->json());
    }

    /**
     * Helper method to convert get response to DTO
     */
    public function getAsDto(string $jobId): BatchJobOut
    {
        $response = $this->get($jobId);

        return BatchJobOut::from($response->json());
    }

    /**
     * Helper method to convert cancel response to DTO
     */
    public function cancelAsDto(string $jobId): BatchJobOut
    {
        $response = $this->cancel($jobId);

        return BatchJobOut::from($response->json());
    }
}
