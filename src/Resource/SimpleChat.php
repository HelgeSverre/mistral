<?php

namespace HelgeSverre\Mistral\Resource;

use Generator;
use HelgeSverre\Mistral\Concerns\HandlesStreamedResponses;
use HelgeSverre\Mistral\Dto\Chat\ChatCompletionRequest;
use HelgeSverre\Mistral\Dto\SimpleChat\SimpleChatResponse;
use HelgeSverre\Mistral\Dto\SimpleChat\SimpleStreamChunk;
use HelgeSverre\Mistral\Enums\Model;
use HelgeSverre\Mistral\Requests\Chat\CreateChatCompletion;
use InvalidArgumentException;
use Saloon\Http\BaseResource;

class SimpleChat extends BaseResource
{
    use HandlesStreamedResponses;

    public function create(
        array $messages,
        string $model = Model::mistral7b->value,
        float $temperature = 0.7,
        int $maxTokens = 2000,
        int $topP = 1,
        bool $safeMode = false,
        bool $jsonMode = false,
        ?int $randomSeed = null
    ): SimpleChatResponse {
        if ($jsonMode) {
            $this->validateJsonModeCompatible($model);
        }

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
                responseFormat: $jsonMode ? ['type' => 'json_object'] : null,
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
     * @return Generator|SimpleStreamChunk[]
     *
     * @noinspection PhpDocSignatureInspection
     * @noinspection PhpDocMissingThrowsInspection
     */
    public function stream(
        array $messages,
        string $model = Model::mistral7b->value,
        float $temperature = 0.7,
        int $maxTokens = 2000,
        int $topP = 1,
        bool $safeMode = false,
        bool $jsonMode = false,
        ?int $randomSeed = null,
    ): Generator {
        if ($jsonMode) {
            $this->validateJsonModeCompatible($model);
        }

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
                responseFormat: $jsonMode ? ['type' => 'json_object'] : null,
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

    protected function validateJsonModeCompatible(string $model): void
    {
        $jsonCompatible = Model::withJsonModeSupport();
        if (in_array($model, $jsonCompatible) === false) {
            throw new InvalidArgumentException('Only '.implode(', ', $jsonCompatible).' models support JSON mode');
        }
    }
}
