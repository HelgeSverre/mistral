<?php

namespace HelgeSverre\Mistral\Requests\Audio;

use HelgeSverre\Mistral\Dto\Audio\SpeechRequest;
use Saloon\Contracts\Body\HasBody;
use Saloon\Enums\Method;
use Saloon\Http\Request;
use Saloon\Traits\Body\HasJsonBody;

/**
 * Create Streaming Speech
 *
 * Generate speech from text and stream the audio data as server-sent events.
 */
class CreateSpeechStreamRequest extends Request implements HasBody
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
        $body['stream'] = true;

        return array_filter($body, fn ($value) => $value !== null);
    }
}
