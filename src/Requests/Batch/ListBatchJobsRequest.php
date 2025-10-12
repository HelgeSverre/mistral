<?php

namespace HelgeSverre\Mistral\Requests\Batch;

use HelgeSverre\Mistral\Dto\Batch\BatchJobsOut;
use Saloon\Enums\Method;
use Saloon\Http\Request;
use Saloon\Http\Response;

/**
 * List batch jobs
 */
class ListBatchJobsRequest extends Request
{
    protected Method $method = Method::GET;

    public function resolveEndpoint(): string
    {
        return '/v1/batch/jobs';
    }

    /**
     * @param  array<string>|null  $status
     */
    public function __construct(
        protected ?int $page = null,
        protected ?int $pageSize = null,
        protected ?string $model = null,
        protected ?string $agentId = null,
        protected ?array $metadata = null,
        protected ?string $createdAfter = null,
        protected ?bool $createdByMe = null,
        protected ?array $status = null,
    ) {}

    protected function defaultQuery(): array
    {
        $query = array_filter([
            'page' => $this->page,
            'page_size' => $this->pageSize,
            'model' => $this->model,
            'agent_id' => $this->agentId,
            'created_after' => $this->createdAfter,
            'created_by_me' => $this->createdByMe,
        ], fn ($value) => $value !== null);

        if ($this->metadata !== null) {
            $query['metadata'] = json_encode($this->metadata);
        }

        if ($this->status !== null) {
            foreach ($this->status as $statusValue) {
                $query['status'][] = $statusValue;
            }
        }

        return $query;
    }

    public function createDtoFromResponse(Response $response): BatchJobsOut
    {
        return BatchJobsOut::from($response->json());
    }
}
