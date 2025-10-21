<?php

namespace HelgeSverre\Mistral\Requests\FineTuning;

use HelgeSverre\Mistral\Dto\FineTuning\JobIn;
use Saloon\Contracts\Body\HasBody;
use Saloon\Enums\Method;
use Saloon\Http\Request;
use Saloon\Traits\Body\HasJsonBody;

/**
 * Create fine-tuning job
 */
class CreateJobRequest extends Request implements HasBody
{
    use HasJsonBody;

    protected Method $method = Method::POST;

    public function resolveEndpoint(): string
    {
        return '/fine_tuning/jobs';
    }

    public function __construct(
        protected JobIn $jobIn,
        protected ?bool $dryRun = null,
    ) {}

    protected function defaultBody(): array
    {
        return array_filter($this->jobIn->toArray());
    }

    protected function defaultQuery(): array
    {
        return array_filter([
            'dry_run' => $this->dryRun,
        ], fn ($value) => $value !== null);
    }
}
