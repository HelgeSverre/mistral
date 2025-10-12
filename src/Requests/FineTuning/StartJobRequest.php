<?php

namespace HelgeSverre\Mistral\Requests\FineTuning;

use Saloon\Enums\Method;
use Saloon\Http\Request;

/**
 * Start validated fine-tuning job
 */
class StartJobRequest extends Request
{
    protected Method $method = Method::POST;

    public function resolveEndpoint(): string
    {
        return "/fine_tuning/jobs/{$this->jobId}/start";
    }

    public function __construct(
        protected string $jobId,
    ) {}
}
