<?php

namespace HelgeSverre\Mistral\Requests\Batch;

use HelgeSverre\Mistral\Dto\Batch\BatchJobOut;
use Saloon\Enums\Method;
use Saloon\Http\Request;
use Saloon\Http\Response;

/**
 * Get batch job details
 */
class GetBatchJobRequest extends Request
{
    protected Method $method = Method::GET;

    public function resolveEndpoint(): string
    {
        return "/v1/batch/jobs/{$this->jobId}";
    }

    public function __construct(
        protected string $jobId,
    ) {}

    public function createDtoFromResponse(Response $response): BatchJobOut
    {
        return BatchJobOut::from($response->json());
    }
}
