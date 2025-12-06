<?php

declare(strict_types=1);

namespace HelgeSverre\Mistral\Dto\Libraries;

use Spatie\LaravelData\Attributes\MapName;
use Spatie\LaravelData\Data;

class DocumentOut extends Data
{
    public function __construct(
        public string $id,
        #[MapName('library_id')]
        public string $libraryId,
        public string $name,
        #[MapName('processing_status')]
        public string $processingStatus,
        #[MapName('created_at')]
        public string $createdAt,
        #[MapName('last_processed_at')]
        public ?string $lastProcessedAt = null,
        public ?int $size = null,
        public ?string $hash = null,
        #[MapName('mime_type')]
        public ?string $mimeType = null,
        public ?string $extension = null,
        public ?string $summary = null,
    ) {}
}
