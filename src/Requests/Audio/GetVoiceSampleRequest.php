<?php

namespace HelgeSverre\Mistral\Requests\Audio;

use Saloon\Enums\Method;
use Saloon\Http\Request;

/**
 * Get Voice Sample
 *
 * Get the audio sample for a voice. Returns raw `audio/wav` bytes via `$response->body()`.
 */
class GetVoiceSampleRequest extends Request
{
    protected Method $method = Method::GET;

    public function __construct(protected string $voiceId) {}

    public function resolveEndpoint(): string
    {
        return "/audio/voices/{$this->voiceId}/sample";
    }
}
