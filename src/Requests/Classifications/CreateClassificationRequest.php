<?php

namespace HelgeSverre\Mistral\Requests\Classifications;

use HelgeSverre\Mistral\Dto\Classifications\ClassificationRequest;
use HelgeSverre\Mistral\Dto\Classifications\ClassificationResponse;
use Saloon\Contracts\Body\HasBody;
use Saloon\Enums\Method;
use Saloon\Http\Request;
use Saloon\Http\Response;
use Saloon\Traits\Body\HasJsonBody;

/**
 * CreateClassification
 *
 * Classify text input
 */
class CreateClassificationRequest extends Request implements HasBody
{
    use HasJsonBody;

    protected Method $method = Method::POST;

    public function resolveEndpoint(): string
    {
        return '/classifications';
    }

    public function __construct(protected ClassificationRequest $classificationRequest) {}

    protected function defaultBody(): array
    {
        return $this->classificationRequest->toArray();
    }

    public function createDtoFromResponse(Response $response): ClassificationResponse
    {
        return ClassificationResponse::from($response->json());
    }
}
