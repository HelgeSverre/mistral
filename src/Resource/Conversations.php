<?php

namespace HelgeSverre\Mistral\Resource;

use Generator;
use HelgeSverre\Mistral\Concerns\HandlesStreamedResponses;
use HelgeSverre\Mistral\Dto\Conversations\ConversationAppendRequest;
use HelgeSverre\Mistral\Dto\Conversations\ConversationRequest;
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
     * Get a specific conversation by ID
     */
    public function get(string $conversationId): Response
    {
        return $this->connector->send(new GetConversationRequest($conversationId));
    }

    /**
     * Append messages to an existing conversation
     */
    public function append(string $conversationId, ConversationAppendRequest $request): Response
    {
        return $this->connector->send(new AppendToConversationRequest($conversationId, $request));
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
     * Get only the messages from a conversation (filtered)
     */
    public function getMessages(string $conversationId): Response
    {
        return $this->connector->send(new GetConversationMessagesRequest($conversationId));
    }

    /**
     * Restart a conversation from a specific entry point
     */
    public function restart(string $conversationId, ConversationRestartRequest $request): Response
    {
        return $this->connector->send(new RestartConversationRequest($conversationId, $request));
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
