<?php

namespace HelgeSverre\Mistral\Resource;

use Generator;
use HelgeSverre\Mistral\Dto\Chat\ChatCompletionRequest;
use HelgeSverre\Mistral\Dto\Chat\StreamedChatCompletionResponse;
use HelgeSverre\Mistral\Dto\SimpleChat\SimpleChatResponse;
use HelgeSverre\Mistral\Enums\Model;
use HelgeSverre\Mistral\Requests\Chat\CreateChatCompletion;
use Psr\Http\Message\StreamInterface;
use Saloon\Http\BaseResource;

class SimpleChat extends BaseResource
{
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
            yield $chatResponse;
        }
    }

    // Credit: https://github.com/openai-php/client/blob/main/src/Responses/StreamResponse.php
    protected function getStreamIterator(StreamInterface $stream): Generator
    {
        while (! $stream->eof()) {
            $line = $this->readLine($stream);

            if (! str_starts_with($line, 'data:')) {
                continue;
            }

            $data = trim(substr($line, strlen('data:')));

            if ($data === '[DONE]') {
                break;
            }

            $response = json_decode($data, true, flags: JSON_THROW_ON_ERROR);

            dump($response);

            yield $response;
            //            yield StreamedChatCompletionResponse::from($response);
        }
    }

    protected function readLine($stream): string
    {
        $buffer = '';
        while (! $stream->eof()) {
            if ('' === ($byte = $stream->read(1))) {
                return $buffer;
            }
            $buffer .= $byte;
            if ($byte === "\n") {
                break;
            }
        }

        return $buffer;
    }
}
