<?php

namespace HelgeSverre\Mistral\Dto\SimpleChat;

use DateTimeImmutable;
use Spatie\LaravelData\Attributes\MapName;
use Spatie\LaravelData\Attributes\WithCast;
use Spatie\LaravelData\Casts\DateTimeInterfaceCast;
use Spatie\LaravelData\Data as SpatieData;

class SimpleStreamChunk extends SpatieData
{
    public function __construct(
        public string $id,
        public string $model,
        public ?string $object,
        #[WithCast(DateTimeInterfaceCast::class, format: 'U')]
        public ?DateTimeImmutable $created,
        public ?string $role,
        public ?string $content,
        #[MapName('finish_reason')]
        public ?string $finishReason

    ) {

    }
}
