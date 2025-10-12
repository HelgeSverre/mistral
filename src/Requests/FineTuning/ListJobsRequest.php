<?php

namespace HelgeSverre\Mistral\Requests\FineTuning;

use HelgeSverre\Mistral\Dto\FineTuning\JobsOut;
use Saloon\Enums\Method;
use Saloon\Http\Request;
use Saloon\Http\Response;

/**
 * List fine-tuning jobs
 */
class ListJobsRequest extends Request
{
    protected Method $method = Method::GET;

    public function resolveEndpoint(): string
    {
        return '/fine_tuning/jobs';
    }

    public function __construct(
        protected ?int $page = null,
        protected ?int $pageSize = null,
        protected ?string $model = null,
        protected ?int $createdAfter = null,
        protected ?int $createdBefore = null,
        protected ?bool $createdByMe = null,
        protected ?string $status = null,
        protected ?string $wandbProject = null,
        protected ?string $wandbName = null,
        protected ?string $suffix = null,
    ) {}

    protected function defaultQuery(): array
    {
        return array_filter([
            'page' => $this->page,
            'page_size' => $this->pageSize,
            'model' => $this->model,
            'created_after' => $this->createdAfter,
            'created_before' => $this->createdBefore,
            'created_by_me' => $this->createdByMe,
            'status' => $this->status,
            'wandb_project' => $this->wandbProject,
            'wandb_name' => $this->wandbName,
            'suffix' => $this->suffix,
        ], fn ($value) => $value !== null);
    }

    public function createDtoFromResponse(Response $response): JobsOut
    {
        return JobsOut::from($response->json());
    }
}
