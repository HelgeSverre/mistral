<?php

namespace HelgeSverre\Mistral\Dto\OCR;

use Spatie\LaravelData\Attributes\DataCollectionOf;
use Spatie\LaravelData\Attributes\MapName;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\DataCollection;

final class OCRResponse extends Data
{
    public function __construct(
        #[DataCollectionOf(Page::class)]
        public DataCollection $pages,
        public string $model,
        #[MapName('document_annotation')]
        public ?string $documentAnnotation = null,
        #[MapName('usage_info')]
        public ?UsageInfo $usageInfo = null,
    ) {}
}
