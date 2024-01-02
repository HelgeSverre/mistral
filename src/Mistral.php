<?php

namespace HelgeSverre\Mistral;

use HelgeSverre\Mistral\Resource\Chat;
use HelgeSverre\Mistral\Resource\Embedding;
use HelgeSverre\Mistral\Resource\Models;
use HelgeSverre\Mistral\Resource\SimpleChat;
use Saloon\Http\Auth\TokenAuthenticator;
use Saloon\Http\Connector;
use Saloon\Traits\Plugins\AcceptsJson;
use SensitiveParameter;

/**
 * Mistral AI API
 *
 * Chat Completion and Embeddings APIs
 */
class Mistral extends Connector
{
    use AcceptsJson;

    public function __construct(
        #[SensitiveParameter] protected readonly string $apiKey,
        protected readonly ?string $baseUrl = null,

    ) {
    }

    protected function defaultAuth(): TokenAuthenticator
    {
        return new TokenAuthenticator($this->apiKey);
    }

    public function resolveBaseUrl(): string
    {
        return $this->baseUrl ?: 'https://api.mistral.ai/v1';
    }

    public function chat(): Chat
    {
        return new Chat($this);
    }

    public function simpleChat(): SimpleChat
    {
        return new SimpleChat($this);
    }

    public function embedding(): Embedding
    {
        return new Embedding($this);
    }

    public function models(): Models
    {
        return new Models($this);
    }
}
