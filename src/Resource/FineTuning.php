<?php

namespace HelgeSverre\Mistral\Resource;

use HelgeSverre\Mistral\Dto\FineTuning\ArchiveFTModelOut;
use HelgeSverre\Mistral\Dto\FineTuning\ClassifierDetailedJobOut;
use HelgeSverre\Mistral\Dto\FineTuning\ClassifierFTModelOut;
use HelgeSverre\Mistral\Dto\FineTuning\ClassifierJobOut;
use HelgeSverre\Mistral\Dto\FineTuning\CompletionDetailedJobOut;
use HelgeSverre\Mistral\Dto\FineTuning\CompletionFTModelOut;
use HelgeSverre\Mistral\Dto\FineTuning\CompletionJobOut;
use HelgeSverre\Mistral\Dto\FineTuning\JobIn;
use HelgeSverre\Mistral\Dto\FineTuning\JobsOut;
use HelgeSverre\Mistral\Dto\FineTuning\LegacyJobMetadataOut;
use HelgeSverre\Mistral\Dto\FineTuning\UnarchiveFTModelOut;
use HelgeSverre\Mistral\Dto\FineTuning\UpdateFTModelIn;
use HelgeSverre\Mistral\Requests\FineTuning\ArchiveModelRequest;
use HelgeSverre\Mistral\Requests\FineTuning\CancelJobRequest;
use HelgeSverre\Mistral\Requests\FineTuning\CreateJobRequest;
use HelgeSverre\Mistral\Requests\FineTuning\GetJobRequest;
use HelgeSverre\Mistral\Requests\FineTuning\ListJobsRequest;
use HelgeSverre\Mistral\Requests\FineTuning\StartJobRequest;
use HelgeSverre\Mistral\Requests\FineTuning\UnarchiveModelRequest;
use HelgeSverre\Mistral\Requests\FineTuning\UpdateModelRequest;
use Saloon\Http\BaseResource;
use Saloon\Http\Response;

class FineTuning extends BaseResource
{
    /**
     * List fine-tuning jobs with optional filters
     */
    public function list(
        ?int $page = null,
        ?int $pageSize = null,
        ?string $model = null,
        ?int $createdAfter = null,
        ?int $createdBefore = null,
        ?bool $createdByMe = null,
        ?string $status = null,
        ?string $wandbProject = null,
        ?string $wandbName = null,
        ?string $suffix = null,
    ): Response {
        return $this->connector->send(new ListJobsRequest(
            page: $page,
            pageSize: $pageSize,
            model: $model,
            createdAfter: $createdAfter,
            createdBefore: $createdBefore,
            createdByMe: $createdByMe,
            status: $status,
            wandbProject: $wandbProject,
            wandbName: $wandbName,
            suffix: $suffix,
        ));
    }

    /**
     * Create a new fine-tuning job
     *
     * @return Response Response containing CompletionJobOut|ClassifierJobOut|LegacyJobMetadataOut
     */
    public function create(JobIn $jobIn, ?bool $dryRun = null): Response
    {
        return $this->connector->send(new CreateJobRequest($jobIn, $dryRun));
    }

    /**
     * Get detailed information about a fine-tuning job
     *
     * @param  string  $jobId  The UUID of the job
     * @return Response Response containing CompletionDetailedJobOut|ClassifierDetailedJobOut
     */
    public function get(string $jobId): Response
    {
        return $this->connector->send(new GetJobRequest($jobId));
    }

    /**
     * Cancel a running fine-tuning job
     *
     * @param  string  $jobId  The UUID of the job
     * @return Response Response containing detailed job object
     */
    public function cancel(string $jobId): Response
    {
        return $this->connector->send(new CancelJobRequest($jobId));
    }

    /**
     * Start a validated fine-tuning job
     *
     * @param  string  $jobId  The UUID of the job
     * @return Response Response containing detailed job object
     */
    public function start(string $jobId): Response
    {
        return $this->connector->send(new StartJobRequest($jobId));
    }

    /**
     * Update fine-tuned model metadata (name and description)
     *
     * @param  string  $modelId  The model ID (e.g., ft:open-mistral-7b:587a6b29:20240514:7e773925)
     * @return Response Response containing CompletionFTModelOut|ClassifierFTModelOut
     */
    public function updateModel(string $modelId, UpdateFTModelIn $updateModel): Response
    {
        return $this->connector->send(new UpdateModelRequest($modelId, $updateModel));
    }

    /**
     * Archive a fine-tuned model
     *
     * @param  string  $modelId  The model ID
     */
    public function archiveModel(string $modelId): Response
    {
        return $this->connector->send(new ArchiveModelRequest($modelId));
    }

    /**
     * Unarchive a fine-tuned model
     *
     * @param  string  $modelId  The model ID
     */
    public function unarchiveModel(string $modelId): Response
    {
        return $this->connector->send(new UnarchiveModelRequest($modelId));
    }

    /**
     * Helper method to convert list response to DTO
     */
    public function listAsDto(
        ?int $page = null,
        ?int $pageSize = null,
        ?string $model = null,
        ?int $createdAfter = null,
        ?int $createdBefore = null,
        ?bool $createdByMe = null,
        ?string $status = null,
        ?string $wandbProject = null,
        ?string $wandbName = null,
        ?string $suffix = null,
    ): JobsOut {
        $response = $this->list(
            page: $page,
            pageSize: $pageSize,
            model: $model,
            createdAfter: $createdAfter,
            createdBefore: $createdBefore,
            createdByMe: $createdByMe,
            status: $status,
            wandbProject: $wandbProject,
            wandbName: $wandbName,
            suffix: $suffix,
        );

        return JobsOut::fromArray($response->json());
    }

    /**
     * Helper to convert create response to appropriate DTO
     */
    public function createAsDto(JobIn $jobIn, ?bool $dryRun = null): CompletionJobOut|ClassifierJobOut|LegacyJobMetadataOut
    {
        $response = $this->create($jobIn, $dryRun);
        $data = $response->json();

        if (isset($data['job_type'])) {
            return match ($data['job_type']) {
                'completion' => CompletionJobOut::from($data),
                'classifier' => ClassifierJobOut::from($data),
                default => CompletionJobOut::from($data),
            };
        }

        return LegacyJobMetadataOut::from($data);
    }

    /**
     * Helper to convert get response to appropriate DTO
     */
    public function getAsDto(string $jobId): CompletionDetailedJobOut|ClassifierDetailedJobOut
    {
        $response = $this->get($jobId);
        $data = $response->json();

        return match ($data['job_type'] ?? '') {
            'completion' => CompletionDetailedJobOut::from($data),
            'classifier' => ClassifierDetailedJobOut::from($data),
            default => CompletionDetailedJobOut::from($data),
        };
    }

    /**
     * Helper to convert cancel response to appropriate DTO
     */
    public function cancelAsDto(string $jobId): CompletionDetailedJobOut|ClassifierDetailedJobOut
    {
        $response = $this->cancel($jobId);
        $data = $response->json();

        return match ($data['job_type'] ?? '') {
            'completion' => CompletionDetailedJobOut::from($data),
            'classifier' => ClassifierDetailedJobOut::from($data),
            default => CompletionDetailedJobOut::from($data),
        };
    }

    /**
     * Helper to convert start response to appropriate DTO
     */
    public function startAsDto(string $jobId): CompletionDetailedJobOut|ClassifierDetailedJobOut
    {
        $response = $this->start($jobId);
        $data = $response->json();

        return match ($data['job_type'] ?? '') {
            'completion' => CompletionDetailedJobOut::from($data),
            'classifier' => ClassifierDetailedJobOut::from($data),
            default => CompletionDetailedJobOut::from($data),
        };
    }

    /**
     * Helper to convert update model response to appropriate DTO
     */
    public function updateModelAsDto(string $modelId, UpdateFTModelIn $updateModel): CompletionFTModelOut|ClassifierFTModelOut
    {
        $response = $this->updateModel($modelId, $updateModel);
        $data = $response->json();

        return match ($data['model_type'] ?? '') {
            'completion' => CompletionFTModelOut::from($data),
            'classifier' => ClassifierFTModelOut::from($data),
            default => CompletionFTModelOut::from($data),
        };
    }

    /**
     * Helper to convert archive response to DTO
     */
    public function archiveModelAsDto(string $modelId): ArchiveFTModelOut
    {
        $response = $this->archiveModel($modelId);

        return ArchiveFTModelOut::from($response->json());
    }

    /**
     * Helper to convert unarchive response to DTO
     */
    public function unarchiveModelAsDto(string $modelId): UnarchiveFTModelOut
    {
        $response = $this->unarchiveModel($modelId);

        return UnarchiveFTModelOut::from($response->json());
    }
}
