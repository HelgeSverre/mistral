<?php

namespace HelgeSverre\Mistral\Dto\OCR;

use Spatie\LaravelData\Attributes\DataCollectionOf;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\DataCollection;

final class Page extends Data
{
    public function __construct(
        public int $index,
        public string $markdown,
        #[DataCollectionOf(Image::class)]
        public DataCollection $images,
        public ?Dimensions $dimensions = null,
    ) {
    }
}
