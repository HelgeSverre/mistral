<?php

namespace HelgeSverre\Mistral\Resource;

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
}
