<?php

declare(strict_types=1);

namespace HelgeSverre\Mistral\Dto\Libraries;

use Spatie\LaravelData\Data;

class LibraryIn extends Data
{
    public function __construct(
        public string $name,
        public ?string $description = null,
    ) {}
}
