<?php

namespace HelgeSverre\Mistral\Requests\FineTuning;

use Saloon\Enums\Method;
use Saloon\Http\Request;

/**
 * Get fine-tuning job details
 */
class GetJobRequest extends Request
{
    protected Method $method = Method::GET;

    public function resolveEndpoint(): string
    {
        return "/fine_tuning/jobs/{$this->jobId}";
    }

    public function __construct(
        protected string $jobId,
    ) {}
}
