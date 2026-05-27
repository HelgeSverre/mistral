<?php

namespace HelgeSverre\Mistral\Requests\Audio;

use HelgeSverre\Mistral\Dto\Audio\SpeechRequest;
use HelgeSverre\Mistral\Dto\Audio\SpeechResponse;
use Saloon\Contracts\Body\HasBody;
use Saloon\Enums\Method;
use Saloon\Http\Request;
use Saloon\Http\Response;
use Saloon\Traits\Body\HasJsonBody;

/**
 * Create Speech
 *
 * Generate speech from text using a saved voice or a reference audio clip.
 */
class CreateSpeechRequest extends Request implements HasBody
{
    use HasJsonBody;

    protected Method $method = Method::POST;

    public function __construct(protected SpeechRequest $speechRequest) {}

    public function resolveEndpoint(): string
    {
        return '/audio/speech';
    }

    protected function defaultBody(): array
    {
        $body = $this->speechRequest->toArray();
        $body['stream'] = false;

        return array_filter($body, fn ($value) => $value !== null);
    }

    public function createDtoFromResponse(Response $response): SpeechResponse
    {
        return SpeechResponse::from($response->json());
    }
}
