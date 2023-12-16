<?php

namespace HelgeSverre\Mistral\Resource;

use Generator;
use HelgeSverre\Mistral\Dto\Chat\ChatCompletionRequest;
use HelgeSverre\Mistral\Enums\Model;
use HelgeSverre\Mistral\Requests\Chat\CreateChatCompletion;
use Saloon\Http\BaseResource;
use Saloon\Http\Response;

class Chat extends BaseResource
{
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

        $stream = $response->stream();
        $buffer = '';

        while (! $stream->eof()) {
            $buffer .= $stream->read(1024);

            // Split buffer by new lines and process each line
            while (($newlinePos = strpos($buffer, "\n\n")) !== false) {

                $line = substr($buffer, 0, $newlinePos);
                $buffer = substr($buffer, $newlinePos + 2);

                if (str_starts_with($line, 'data:')) {
                    $part = trim(substr($line, 5)); // Remove 'data:' prefix

                    if ($part == '[DONE]') {
                        return;
                    }

                    yield json_decode($part, true, flags: JSON_THROW_ON_ERROR);
                }
            }
        }
    }
}
