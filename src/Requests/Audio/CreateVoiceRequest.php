<?php

namespace HelgeSverre\Mistral\Requests\Audio;

use HelgeSverre\Mistral\Dto\Audio\VoiceCreateRequest as VoiceCreateData;
use HelgeSverre\Mistral\Dto\Audio\VoiceResponse;
use Saloon\Contracts\Body\HasBody;
use Saloon\Enums\Method;
use Saloon\Http\Request;
use Saloon\Http\Response;
use Saloon\Traits\Body\HasJsonBody;

/**
 * Create Voice
 *
 * Create a new voice with a base64-encoded audio sample.
 */
class CreateVoiceRequest extends Request implements HasBody
{
    use HasJsonBody;

    protected Method $method = Method::POST;

    public function __construct(protected VoiceCreateData $voiceCreateRequest) {}

    public function resolveEndpoint(): string
    {
        return '/audio/voices';
    }

    protected function defaultBody(): array
    {
        return array_filter(
            $this->voiceCreateRequest->toArray(),
            fn ($value) => $value !== null,
        );
    }

    public function createDtoFromResponse(Response $response): VoiceResponse
    {
        return VoiceResponse::from($response->json());
    }
}
