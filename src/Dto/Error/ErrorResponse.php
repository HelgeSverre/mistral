<?php

namespace HelgeSverre\Mistral\Dto\Error;

use Spatie\LaravelData\Data as SpatieData;

class ErrorResponse extends SpatieData
{
    public function __construct(
        public ?Error $error = null,
    ) {
    }
}
