<?php

namespace HelgeSverre\Mistral\Requests\Audio;

use HelgeSverre\Mistral\Dto\Audio\VoiceResponse;
use Saloon\Enums\Method;
use Saloon\Http\Request;
use Saloon\Http\Response;

/**
 * Delete Voice
 *
 * Delete a custom voice.
 */
class DeleteVoiceRequest extends Request
{
    protected Method $method = Method::DELETE;

    public function __construct(protected string $voiceId) {}

    public function resolveEndpoint(): string
    {
        return "/audio/voices/{$this->voiceId}";
    }

    public function createDtoFromResponse(Response $response): VoiceResponse
    {
        return VoiceResponse::from($response->json());
    }
}
