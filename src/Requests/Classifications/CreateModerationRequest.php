<?php

namespace HelgeSverre\Mistral\Requests\Classifications;

use HelgeSverre\Mistral\Dto\Classifications\ClassificationRequest;
use HelgeSverre\Mistral\Dto\Moderations\ModerationResponse;
use Saloon\Contracts\Body\HasBody;
use Saloon\Enums\Method;
use Saloon\Http\Request;
use Saloon\Http\Response;
use Saloon\Traits\Body\HasJsonBody;

/**
 * CreateModerationRequest
 *
 * Moderate text input
 */
class CreateModerationRequest extends Request implements HasBody
{
    use HasJsonBody;

    protected Method $method = Method::POST;

    public function resolveEndpoint(): string
    {
        return '/moderations';
    }

    public function __construct(protected ClassificationRequest $classificationRequest) {}

    protected function defaultBody(): array
    {
        return $this->classificationRequest->toArray();
    }

    public function createDtoFromResponse(Response $response): ModerationResponse
    {
        return ModerationResponse::from($response->json());
    }
}
