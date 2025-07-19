<?php

namespace HelgeSverre\Mistral\Dto\OCR;

use Spatie\LaravelData\Data;

final class Dimensions extends Data
{
    public function __construct(
        public int $dpi,
        public int $height,
        public int $width,
    ) {
    }
}
