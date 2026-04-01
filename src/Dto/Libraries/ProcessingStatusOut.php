<?php

declare(strict_types=1);

namespace HelgeSverre\Mistral\Dto\Libraries;

use Spatie\LaravelData\Attributes\MapName;
use Spatie\LaravelData\Data;

class ProcessingStatusOut extends Data
{
    public function __construct(
        #[MapName('document_id')]
        public string $documentId,
        #[MapName('processing_status')]
        public string $processingStatus,
    ) {}
}
