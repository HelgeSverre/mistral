<?php

namespace HelgeSverre\Mistral\Requests\Audio;

use HelgeSverre\Mistral\Dto\Audio\VoiceResponse;
use HelgeSverre\Mistral\Dto\Audio\VoiceUpdateRequest as VoiceUpdateData;
use Saloon\Contracts\Body\HasBody;
use Saloon\Enums\Method;
use Saloon\Http\Request;
use Saloon\Http\Response;
use Saloon\Traits\Body\HasJsonBody;

/**
 * Update Voice
 *
 * Update voice metadata (name, gender, languages, age, tags).
 */
class UpdateVoiceRequest extends Request implements HasBody
{
    use HasJsonBody;

    protected Method $method = Method::PATCH;

    public function __construct(
        protected string $voiceId,
        protected VoiceUpdateData $voiceUpdateRequest,
    ) {}

    public function resolveEndpoint(): string
    {
        return "/audio/voices/{$this->voiceId}";
    }

    protected function defaultBody(): array
    {
        return array_filter(
            $this->voiceUpdateRequest->toArray(),
            fn ($value) => $value !== null,
        );
    }

    public function createDtoFromResponse(Response $response): VoiceResponse
    {
        return VoiceResponse::from($response->json());
    }
}
