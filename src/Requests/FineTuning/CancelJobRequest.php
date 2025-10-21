<?php

namespace HelgeSverre\Mistral\Requests\FineTuning;

use Saloon\Enums\Method;
use Saloon\Http\Request;

/**
 * Cancel fine-tuning job
 */
class CancelJobRequest extends Request
{
    protected Method $method = Method::POST;

    public function resolveEndpoint(): string
    {
        return "/fine_tuning/jobs/{$this->jobId}/cancel";
    }

    public function __construct(
        protected string $jobId,
    ) {}
}
