<?php

namespace HelgeSverre\Mistral\Resource;

use HelgeSverre\Mistral\Dto\Classifications\ChatClassificationRequest;
use HelgeSverre\Mistral\Dto\Classifications\ClassificationRequest;
use HelgeSverre\Mistral\Dto\Classifications\ClassificationResponse;
use HelgeSverre\Mistral\Dto\Moderations\ChatModerationRequest;
use HelgeSverre\Mistral\Dto\Moderations\ModerationResponse;
use HelgeSverre\Mistral\Requests\Classifications\CreateChatClassificationRequest;
use HelgeSverre\Mistral\Requests\Classifications\CreateChatModerationRequest;
use HelgeSverre\Mistral\Requests\Classifications\CreateClassificationRequest;
use HelgeSverre\Mistral\Requests\Classifications\CreateModerationRequest;
use Saloon\Http\BaseResource;
use Saloon\Http\Response;

class Classifications extends BaseResource
{
    // Moderation endpoints (tagged as 'classifiers' in OpenAPI spec)

    public function moderate(
        string $model,
        string|array $input
    ): Response {
        $request = new ClassificationRequest(
            model: $model,
            input: $input
        );

        return $this->connector->send(new CreateModerationRequest($request));
    }

    public function moderateAsDto(
        string $model,
        string|array $input
    ): ModerationResponse {
        return $this->moderate($model, $input)->dto();
    }

    public function moderateChat(
        string $model,
        array $input
    ): Response {
        $request = new ChatModerationRequest(
            model: $model,
            input: $input
        );

        return $this->connector->send(new CreateChatModerationRequest($request));
    }

    public function moderateChatAsDto(
        string $model,
        array $input
    ): ModerationResponse {
        return $this->moderateChat($model, $input)->dto();
    }

    // Classification endpoints

    public function classify(
        string $model,
        string|array $input
    ): Response {
        $request = new ClassificationRequest(
            model: $model,
            input: $input
        );

        return $this->connector->send(new CreateClassificationRequest($request));
    }

    public function classifyAsDto(
        string $model,
        string|array $input
    ): ClassificationResponse {
        return $this->classify($model, $input)->dto();
    }

    public function classifyChat(
        string $model,
        array $messages
    ): Response {
        $request = new ChatClassificationRequest(
            model: $model,
            messages: $messages
        );

        return $this->connector->send(new CreateChatClassificationRequest($request));
    }

    public function classifyChatAsDto(
        string $model,
        array $messages
    ): ClassificationResponse {
        return $this->classifyChat($model, $messages)->dto();
    }
}
