<?php

namespace HelgeSverre\Mistral\Requests\Batch;

use HelgeSverre\Mistral\Dto\Batch\BatchJobIn;
use HelgeSverre\Mistral\Dto\Batch\BatchJobOut;
use Saloon\Contracts\Body\HasBody;
use Saloon\Enums\Method;
use Saloon\Http\Request;
use Saloon\Http\Response;
use Saloon\Traits\Body\HasJsonBody;

/**
 * Create a new batch job
 */
class CreateBatchJobRequest extends Request implements HasBody
{
    use HasJsonBody;

    protected Method $method = Method::POST;

    public function resolveEndpoint(): string
    {
        return '/v1/batch/jobs';
    }

    public function __construct(
        protected BatchJobIn $batchJobIn,
    ) {}

    protected function defaultBody(): array
    {
        return $this->batchJobIn->toArray();
    }

    public function createDtoFromResponse(Response $response): BatchJobOut
    {
        return BatchJobOut::from($response->json());
    }
}
