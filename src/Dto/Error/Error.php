<?php

namespace HelgeSverre\Mistral\Dto\Error;

use Spatie\LaravelData\Data as SpatieData;

class Error extends SpatieData
{
    public function __construct(
        public ?string $type = null,
        public ?string $message = null,
        public ?string $param = null,
        public ?string $code = null,
    ) {
    }
}
