<?php

namespace HelgeSverre\Mistral\Requests\FineTuning;

use HelgeSverre\Mistral\Dto\FineTuning\UpdateFTModelIn;
use Saloon\Contracts\Body\HasBody;
use Saloon\Enums\Method;
use Saloon\Http\Request;
use Saloon\Traits\Body\HasJsonBody;

/**
 * Update fine-tuned model metadata
 */
class UpdateModelRequest extends Request implements HasBody
{
    use HasJsonBody;

    protected Method $method = Method::PATCH;

    public function resolveEndpoint(): string
    {
        return "/fine_tuning/models/{$this->modelId}";
    }

    public function __construct(
        protected string $modelId,
        protected UpdateFTModelIn $updateModel,
    ) {}

    protected function defaultBody(): array
    {
        return array_filter($this->updateModel->toArray());
    }
}
