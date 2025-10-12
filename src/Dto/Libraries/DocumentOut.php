<?php

declare(strict_types=1);

namespace HelgeSverre\Mistral\Dto\Libraries;

use HelgeSverre\Mistral\Enums\DocumentStatus;
use Spatie\LaravelData\Attributes\MapName;
use Spatie\LaravelData\Data;

class DocumentOut extends Data
{
    public function __construct(
        public string $id,
        #[MapName('library_id')]
        public string $libraryId,
        public string $name,
        public DocumentStatus $status,
        #[MapName('created_at')]
        public string $createdAt,
        #[MapName('updated_at')]
        public string $updatedAt,
        #[MapName('size_bytes')]
        public ?int $sizeBytes = null,
        #[MapName('num_chunks')]
        public ?int $numChunks = null,
    ) {}
}
