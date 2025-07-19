<?php

namespace HelgeSverre\Mistral\Dto\OCR;

use Spatie\LaravelData\Attributes\MapName;
use Spatie\LaravelData\Data;

final class Image extends Data
{
    public function __construct(
        public string $id,
        #[MapName('top_left_x')]
        public int $topLeftX,
        #[MapName('top_left_y')]
        public int $topLeftY,
        #[MapName('bottom_right_x')]
        public int $bottomRightX,
        #[MapName('bottom_right_y')]
        public int $bottomRightY,
        #[MapName('image_base64')]
        public ?string $imageBase64 = null,
        #[MapName('image_annotation')]
        public ?string $imageAnnotation = null,
    ) {
    }
}
