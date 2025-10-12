<?php

namespace HelgeSverre\Mistral\Requests\Classifications;

use HelgeSverre\Mistral\Dto\Classifications\ChatClassificationRequest;
use HelgeSverre\Mistral\Dto\Moderations\ModerationResponse;
use Saloon\Contracts\Body\HasBody;
use Saloon\Enums\Method;
use Saloon\Http\Request;
use Saloon\Http\Response;
use Saloon\Traits\Body\HasJsonBody;

/**
 * CreateChatModerationRequest
 *
 * Moderate chat conversation
 */
class CreateChatModerationRequest extends Request implements HasBody
{
    use HasJsonBody;

    protected Method $method = Method::POST;

    public function resolveEndpoint(): string
    {
        return '/chat/moderations';
    }

    public function __construct(protected ChatClassificationRequest $chatClassificationRequest) {}

    protected function defaultBody(): array
    {
        return $this->chatClassificationRequest->toArray();
    }

    public function createDtoFromResponse(Response $response): ModerationResponse
    {
        return ModerationResponse::from($response->json());
    }
}
