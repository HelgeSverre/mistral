<?php

declare(strict_types=1);

namespace HelgeSverre\Mistral\Dto\Libraries;

use Spatie\LaravelData\Data;

class DocumentTextContent extends Data
{
    public function __construct(
        public string $text,
    ) {}
}
