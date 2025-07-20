<?php

namespace HelgeSverre\Mistral\Dto\OCR;

use Spatie\LaravelData\Attributes\MapName;
use Spatie\LaravelData\Data;

final class UsageInfo extends Data
{
    public function __construct(
        #[MapName('pages_processed')]
        public int $pagesProcessed,
        #[MapName('doc_size_bytes')]
        public int $docSizeBytes,
    ) {}
}
