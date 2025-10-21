<?php

namespace HelgeSverre\Mistral\Resource;

use Generator;
use HelgeSverre\Mistral\Concerns\HandlesStreamedResponses;
use HelgeSverre\Mistral\Dto\Conversations\ConversationAppendRequest;
use HelgeSverre\Mistral\Dto\Conversations\ConversationHistory;
use HelgeSverre\Mistral\Dto\Conversations\ConversationList;
use HelgeSverre\Mistral\Dto\Conversations\ConversationMessages;
use HelgeSverre\Mistral\Dto\Conversations\ConversationRequest;
use HelgeSverre\Mistral\Dto\Conversations\ConversationResponse;
use HelgeSverre\Mistral\Dto\Conversations\ConversationRestartRequest;
use HelgeSverre\Mistral\Requests\Conversations\AppendToConversationRequest;
use HelgeSverre\Mistral\Requests\Conversations\AppendToConversationStreamRequest;
use HelgeSverre\Mistral\Requests\Conversations\CreateConversationRequest;
use HelgeSverre\Mistral\Requests\Conversations\CreateConversationStreamRequest;
use HelgeSverre\Mistral\Requests\Conversations\GetConversationHistoryRequest;
use HelgeSverre\Mistral\Requests\Conversations\GetConversationMessagesRequest;
use HelgeSverre\Mistral\Requests\Conversations\GetConversationRequest;
use HelgeSverre\Mistral\Requests\Conversations\ListConversationsRequest;
use HelgeSverre\Mistral\Requests\Conversations\RestartConversationRequest;
use HelgeSverre\Mistral\Requests\Conversations\RestartConversationStreamRequest;
use Saloon\Http\BaseResource;
use Saloon\Http\Response;

class Conversations extends BaseResource
{
    use HandlesStreamedResponses;

    /**
     * Create a new conversation with a model or agent
     */
    public function create(ConversationRequest $request): Response
    {
        return $this->connector->send(new CreateConversationRequest($request));
    }

    /**
     * Create a new conversation and return typed DTO
     */
    public function createDto(ConversationRequest $request): ConversationResponse
    {
        return $this->create($request)->dto();
    }

    /**
     * Create a new conversation with streaming
     *
     * @return Generator<array>
     */
    public function createStreamed(ConversationRequest $request): Generator
    {
        $response = $this->connector->send(new CreateConversationStreamRequest($request));

        foreach ($this->getStreamIterator($response->stream()) as $chunk) {
            yield $chunk;
        }
    }

    /**
     * List all conversations with optional pagination
     */
    public function list(?int $page = null, ?int $pageSize = null, ?string $order = null): Response
    {
        return $this->connector->send(new ListConversationsRequest($page, $pageSize, $order));
    }

    /**
     * List all conversations with optional pagination and return typed DTO
     */
    public function listDto(?int $page = null, ?int $pageSize = null, ?string $order = null): ConversationList
    {
        return $this->list($page, $pageSize, $order)->dto();
    }

    /**
     * Get a specific conversation by ID
     */
    public function get(string $conversationId): Response
    {
        return $this->connector->send(new GetConversationRequest($conversationId));
    }

    /**
     * Get a specific conversation by ID and return typed DTO
     */
    public function getDto(string $conversationId): ConversationResponse
    {
        return $this->get($conversationId)->dto();
    }

    /**
     * Append messages to an existing conversation
     */
    public function append(string $conversationId, ConversationAppendRequest $request): Response
    {
        return $this->connector->send(new AppendToConversationRequest($conversationId, $request));
    }

    /**
     * Append messages to an existing conversation and return typed DTO
     */
    public function appendDto(string $conversationId, ConversationAppendRequest $request): ConversationResponse
    {
        return $this->append($conversationId, $request)->dto();
    }

    /**
     * Append messages to an existing conversation with streaming
     *
     * @return Generator<array>
     */
    public function appendStreamed(string $conversationId, ConversationAppendRequest $request): Generator
    {
        $response = $this->connector->send(new AppendToConversationStreamRequest($conversationId, $request));

        foreach ($this->getStreamIterator($response->stream()) as $chunk) {
            yield $chunk;
        }
    }

    /**
     * Get the full conversation history (all entries)
     */
    public function getHistory(string $conversationId): Response
    {
        return $this->connector->send(new GetConversationHistoryRequest($conversationId));
    }

    /**
     * Get the full conversation history and return typed DTO
     */
    public function getHistoryDto(string $conversationId): ConversationHistory
    {
        return $this->getHistory($conversationId)->dto();
    }

    /**
     * Get only the messages from a conversation (filtered)
     */
    public function getMessages(string $conversationId): Response
    {
        return $this->connector->send(new GetConversationMessagesRequest($conversationId));
    }

    /**
     * Get only the messages from a conversation and return typed DTO
     */
    public function getMessagesDto(string $conversationId): ConversationMessages
    {
        return $this->getMessages($conversationId)->dto();
    }

    /**
     * Restart a conversation from a specific entry point
     */
    public function restart(string $conversationId, ConversationRestartRequest $request): Response
    {
        return $this->connector->send(new RestartConversationRequest($conversationId, $request));
    }

    /**
     * Restart a conversation from a specific entry point and return typed DTO
     */
    public function restartDto(string $conversationId, ConversationRestartRequest $request): ConversationResponse
    {
        return $this->restart($conversationId, $request)->dto();
    }

    /**
     * Restart a conversation from a specific entry point with streaming
     *
     * @return Generator<array>
     */
    public function restartStreamed(string $conversationId, ConversationRestartRequest $request): Generator
    {
        $response = $this->connector->send(new RestartConversationStreamRequest($conversationId, $request));

        foreach ($this->getStreamIterator($response->stream()) as $chunk) {
            yield $chunk;
        }
    }
}
