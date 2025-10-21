<?php

namespace HelgeSverre\Mistral\Dto\Files;

use Spatie\LaravelData\Data;

final class FileSignedURL extends Data
{
    public function __construct(
        public string $url,
    ) {}
}
