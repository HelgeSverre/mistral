<?php

namespace HelgeSverre\Mistral\Requests\Classifications;

use HelgeSverre\Mistral\Dto\Classifications\ChatClassificationRequest;
use HelgeSverre\Mistral\Dto\Classifications\ClassificationResponse;
use Saloon\Contracts\Body\HasBody;
use Saloon\Enums\Method;
use Saloon\Http\Request;
use Saloon\Http\Response;
use Saloon\Traits\Body\HasJsonBody;

/**
 * CreateChatClassification
 *
 * Classify chat conversation
 */
class CreateChatClassificationRequest extends Request implements HasBody
{
    use HasJsonBody;

    protected Method $method = Method::POST;

    public function resolveEndpoint(): string
    {
        return '/chat/classifications';
    }

    public function __construct(protected ChatClassificationRequest $chatClassificationRequest) {}

    protected function defaultBody(): array
    {
        return $this->chatClassificationRequest->toArray();
    }

    public function createDtoFromResponse(Response $response): ClassificationResponse
    {
        return ClassificationResponse::from($response->json());
    }
}
