<?php

namespace HelgeSverre\Mistral\Dto\OCR;

use Spatie\LaravelData\Attributes\MapName;
use Spatie\LaravelData\Data;

final class OCRRequest extends Data
{
    public function __construct(
        public string $model,
        public Document $document,
        #[MapName('include_image_base64')]
        public ?bool $includeImageBase64 = null,
    ) {
    }

    public function toArray(): array
    {
        $data = [
            'model' => $this->model,
            'document' => $this->document->toArray(),
        ];

        if ($this->includeImageBase64 !== null) {
            $data['include_image_base64'] = $this->includeImageBase64;
        }

        return $data;
    }
}
