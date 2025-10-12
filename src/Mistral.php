<?php

namespace HelgeSverre\Mistral;

use HelgeSverre\Mistral\Resource\Agents;
use HelgeSverre\Mistral\Resource\Audio;
use HelgeSverre\Mistral\Resource\Batch;
use HelgeSverre\Mistral\Resource\Chat;
use HelgeSverre\Mistral\Resource\Classifications;
use HelgeSverre\Mistral\Resource\Conversations;
use HelgeSverre\Mistral\Resource\Embedding;
use HelgeSverre\Mistral\Resource\Files;
use HelgeSverre\Mistral\Resource\Fim;
use HelgeSverre\Mistral\Resource\FineTuning;
use HelgeSverre\Mistral\Resource\Libraries;
use HelgeSverre\Mistral\Resource\Models;
use HelgeSverre\Mistral\Resource\Moderations;
use HelgeSverre\Mistral\Resource\OCR;
use HelgeSverre\Mistral\Resource\SimpleChat;
use Saloon\Http\Auth\TokenAuthenticator;
use Saloon\Http\Connector;
use Saloon\Traits\Plugins\AcceptsJson;
use Saloon\Traits\Plugins\HasTimeout;
use SensitiveParameter;

/**
 * Mistral AI API
 *
 * Chat Completion and Embeddings APIs
 */
class Mistral extends Connector
{
    use AcceptsJson;
    use HasTimeout;

    public function __construct(
        #[SensitiveParameter] protected readonly string $apiKey,
        protected readonly ?string $baseUrl = null,
        protected ?int $timeout = 60,
    ) {}

    public function getConnectTimeout(): float
    {
        return $this->timeout;
    }

    public function getRequestTimeout(): float
    {
        return $this->timeout;
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

    public function ocr(): OCR
    {
        return new OCR($this);
    }

    public function files(): Files
    {
        return new Files($this);
    }

    public function fineTuning(): FineTuning
    {
        return new FineTuning($this);
    }

    public function fim(): Fim
    {
        return new Fim($this);
    }

    public function audio(): Audio
    {
        return new Audio($this);
    }

    public function batch(): Batch
    {
        return new Batch($this);
    }

    public function moderations(): Moderations
    {
        return new Moderations($this);
    }

    public function classifications(): Classifications
    {
        return new Classifications($this);
    }

    public function conversations(): Conversations
    {
        return new Conversations($this);
    }

    public function agents(): Agents
    {
        return new Agents($this);
    }

    public function libraries(): Libraries
    {
        return new Libraries($this);
    }
}
