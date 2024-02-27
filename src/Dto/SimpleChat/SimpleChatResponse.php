<?php

namespace HelgeSverre\Mistral\Dto\SimpleChat;

use DateTimeImmutable;
use Spatie\LaravelData\Attributes\MapName;
use Spatie\LaravelData\Attributes\WithCast;
use Spatie\LaravelData\Casts\DateTimeInterfaceCast;
use Spatie\LaravelData\Data as SpatieData;

/**
 * Simplified Chat Response DTO for convenience when doing simple chat completions.
 *
 * Essentially it is a condensed and less nested version of the ChatCompletionResponse without multiple choices.
 *
 * @property-read string $id
 * @property-read string $object
 * @property-read DateTimeImmutable $created
 * @property-read string $role
 * @property-read string $content
 * @property-read string $model
 * @property-read int $promptTokens
 * @property-read int $completionTokens
 * @property-read int $totalTokens
 */
class SimpleChatResponse extends SpatieData
{
    public function __construct(
        public string $id,
        public string $object,
        public string $role,
        public string $content,
        public string $model,

        #[WithCast(DateTimeInterfaceCast::class, format: 'U')]
        public DateTimeImmutable $created,
        #[MapName('prompt_tokens')]
        public int $promptTokens,
        #[MapName('completion_tokens')]
        public int $completionTokens,
        #[MapName('total_tokens')]
        public int $totalTokens,
        #[MapName('finish_reason')]
        public ?string $finishReason,
    ) {
    }

    public function contentAsJson(): ?array
    {
        return json_decode($this->content, true);
    }
}
