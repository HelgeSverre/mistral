<?php

namespace HelgeSverre\Mistral\Resource;

use HelgeSverre\Mistral\Dto\Moderations\ChatModerationRequest;
use HelgeSverre\Mistral\Dto\Moderations\ClassificationRequest;
use HelgeSverre\Mistral\Dto\Moderations\ModerationResponse;
use HelgeSverre\Mistral\Requests\Moderations\CreateChatModerationRequest;
use HelgeSverre\Mistral\Requests\Moderations\CreateModerationRequest;
use Saloon\Http\BaseResource;
use Saloon\Http\Response;

class Moderations extends BaseResource
{
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
        array $messages
    ): Response {
        $request = new ChatModerationRequest(
            model: $model,
            messages: $messages
        );

        return $this->connector->send(new CreateChatModerationRequest($request));
    }

    public function moderateChatAsDto(
        string $model,
        array $messages
    ): ModerationResponse {
        return $this->moderateChat($model, $messages)->dto();
    }
}
