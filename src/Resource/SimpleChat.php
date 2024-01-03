<?php

namespace HelgeSverre\Mistral\Resource;

use Generator;
use HelgeSverre\Mistral\Concerns\HandlesStreamedResponses;
use HelgeSverre\Mistral\Dto\Chat\ChatCompletionRequest;
use HelgeSverre\Mistral\Dto\Chat\StreamedChatCompletionResponse;
use HelgeSverre\Mistral\Dto\SimpleChat\SimpleChatResponse;
use HelgeSverre\Mistral\Dto\SimpleChat\SimpleStreamChunk;
use HelgeSverre\Mistral\Enums\Model;
use HelgeSverre\Mistral\Requests\Chat\CreateChatCompletion;
use Saloon\Http\BaseResource;

class SimpleChat extends BaseResource
{
    use HandlesStreamedResponses;

    public function create(
        array $messages,
        string $model = Model::tiny->value,
        float $temperature = 0.7,
        int $maxTokens = 2000,
        int $topP = 1,
        bool $safeMode = false,
        ?int $randomSeed = null
    ): SimpleChatResponse {
        $response = $this->connector->send(new CreateChatCompletion(
            new ChatCompletionRequest(
                model: $model,
                messages: $messages,
                temperature: $temperature,
                topP: $topP,
                maxTokens: $maxTokens,
                stream: false,
                safeMode: $safeMode,
                randomSeed: $randomSeed,
            )
        ));

        return SimpleChatResponse::from([
            'id' => $response->json('id'),
            'object' => $response->json('object'),
            'created' => $response->json('created'),
            'role' => $response->json('choices.0.message.role'),
            'content' => $response->json('choices.0.message.content'),
            'finishReason' => $response->json('choices.0.finish_reason'),
            'model' => $response->json('model'),
            'promptTokens' => $response->json('usage.prompt_tokens'),
            'completionTokens' => $response->json('usage.completion_tokens'),
            'totalTokens' => $response->json('usage.total_tokens'),
        ]);
    }

    /**
     * @return Generator|StreamedChatCompletionResponse[]
     *
     * @noinspection PhpDocSignatureInspection
     * @noinspection PhpDocMissingThrowsInspection
     */
    public function stream(
        array $messages,
        string $model = Model::tiny->value,
        float $temperature = 0.7,
        int $maxTokens = 2000,
        int $topP = 1,
        bool $safeMode = false,
        ?int $randomSeed = null,
    ): Generator {
        $response = $this->connector->send(new CreateChatCompletion(
            new ChatCompletionRequest(
                model: $model,
                messages: $messages,
                temperature: $temperature,
                topP: $topP,
                maxTokens: $maxTokens,
                stream: true,
                safeMode: $safeMode,
                randomSeed: $randomSeed,
            )
        ));

        foreach ($this->getStreamIterator($response->stream()) as $chatResponse) {
            yield SimpleStreamChunk::from([
                'id' => $chatResponse['id'] ?? null,
                'model' => $chatResponse['model'] ?? null,
                'object' => $chatResponse['object'] ?? null,
                'created' => $chatResponse['created'] ?? null,
                'role' => $chatResponse['choices'][0]['delta']['role'] ?? null,
                'content' => $chatResponse['choices'][0]['delta']['content'] ?? null,
                'finishReason' => $chatResponse['choices'][0]['finish_reason'] ?? null,
            ]);
        }
    }
}
