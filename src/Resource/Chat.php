<?php

namespace HelgeSverre\Mistral\Resource;

use Generator;
use HelgeSverre\Mistral\Concerns\HandlesStreamedResponses;
use HelgeSverre\Mistral\Dto\Chat\ChatCompletionRequest;
use HelgeSverre\Mistral\Dto\Chat\StreamedChatCompletionResponse;
use HelgeSverre\Mistral\Enums\Model;
use HelgeSverre\Mistral\Requests\Chat\CreateChatCompletion;
use Saloon\Http\BaseResource;
use Saloon\Http\Response;

class Chat extends BaseResource
{
    use HandlesStreamedResponses;

    public function create(
        array $messages,
        string $model = Model::tiny->value,
        float $temperature = 0.7,
        int $maxTokens = 2000,
        int $topP = 1,
        bool $safeMode = false,
        bool $stream = false,
        ?int $randomSeed = null
    ): Response {
        return $this->connector->send(new CreateChatCompletion(
            new ChatCompletionRequest(
                model: $model,
                messages: $messages,
                temperature: $temperature,
                topP: $topP,
                maxTokens: $maxTokens,
                stream: $stream,
                safeMode: $safeMode,
                randomSeed: $randomSeed,
            )
        ));
    }

    /**
     * @return Generator|StreamedChatCompletionResponse[]
     *
     * @noinspection PhpDocSignatureInspection
     * @noinspection PhpDocMissingThrowsInspection
     */
    public function createStreamed(
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
            yield StreamedChatCompletionResponse::from($chatResponse);
        }
    }
}
