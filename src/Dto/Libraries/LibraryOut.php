<?php

declare(strict_types=1);

namespace HelgeSverre\Mistral\Dto\Libraries;

use Spatie\LaravelData\Attributes\MapName;
use Spatie\LaravelData\Data;

class LibraryOut extends Data
{
    public function __construct(
        public string $id,
        public string $name,
        public ?string $description,
        #[MapName('created_at')]
        public string $createdAt,
        #[MapName('updated_at')]
        public string $updatedAt,
    ) {}
}
