<?php

namespace HelgeSverre\Mistral\Dto\OCR;

use Spatie\LaravelData\Attributes\DataCollectionOf;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\DataCollection;

final class OCRResponse extends Data
{
    public function __construct(
        #[DataCollectionOf(Page::class)]
        public DataCollection $pages,
    ) {
    }
}
