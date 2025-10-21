<?php

namespace HelgeSverre\Mistral\Resource;

use Generator;
use HelgeSverre\Mistral\Concerns\HandlesStreamedResponses;
use HelgeSverre\Mistral\Dto\Chat\ChatCompletionRequest;
use HelgeSverre\Mistral\Dto\Chat\ChatCompletionResponse;
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
        string $model = Model::mistral7b->value,
        float $temperature = 0.7,
        int $maxTokens = 2000,
        float $topP = 1.0,
        bool $safeMode = false,
        bool $stream = false,
        ?int $randomSeed = null,
        ?array $tools = null,
        ?string $toolChoice = null,
        ?array $responseFormat = null,
        string|array|null $stop = null,
        ?float $presencePenalty = null,
        ?float $frequencyPenalty = null,
        ?array $prediction = null,
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
                tools: $tools,
                toolChoice: $toolChoice,
                responseFormat: $responseFormat,
                stop: $stop,
                presencePenalty: $presencePenalty,
                frequencyPenalty: $frequencyPenalty,
                prediction: $prediction,
            )
        ));
    }

    /**
     * Create chat completion and return typed DTO
     */
    public function createDto(
        array $messages,
        string $model = Model::mistral7b->value,
        float $temperature = 0.7,
        int $maxTokens = 2000,
        float $topP = 1.0,
        bool $safeMode = false,
        ?int $randomSeed = null,
        ?array $tools = null,
        ?string $toolChoice = null,
        ?array $responseFormat = null,
        string|array|null $stop = null,
        ?float $presencePenalty = null,
        ?float $frequencyPenalty = null,
        ?array $prediction = null,
    ): ChatCompletionResponse {
        return $this->create(
            messages: $messages,
            model: $model,
            temperature: $temperature,
            maxTokens: $maxTokens,
            topP: $topP,
            safeMode: $safeMode,
            stream: false, // DTO method always non-streaming
            randomSeed: $randomSeed,
            tools: $tools,
            toolChoice: $toolChoice,
            responseFormat: $responseFormat,
            stop: $stop,
            presencePenalty: $presencePenalty,
            frequencyPenalty: $frequencyPenalty,
            prediction: $prediction,
        )->dto();
    }

    /**
     * @return Generator|StreamedChatCompletionResponse[]
     *
     * @noinspection PhpDocSignatureInspection
     * @noinspection PhpDocMissingThrowsInspection
     */
    public function createStreamed(
        array $messages,
        string $model = Model::small->value,
        float $temperature = 0.7,
        int $maxTokens = 2000,
        int $topP = 1,
        bool $safeMode = false,
        ?int $randomSeed = null,
        ?array $responseFormat = null,
        string|array|null $stop = null,
        ?float $presencePenalty = null,
        ?float $frequencyPenalty = null,
        ?array $prediction = null,
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
                responseFormat: $responseFormat,
                stop: $stop,
                presencePenalty: $presencePenalty,
                frequencyPenalty: $frequencyPenalty,
                prediction: $prediction,
            )
        ));

        foreach ($this->getStreamIterator($response->stream()) as $chatResponse) {
            yield StreamedChatCompletionResponse::from($chatResponse);
        }
    }
}
