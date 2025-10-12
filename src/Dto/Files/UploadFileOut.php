<?php

namespace HelgeSverre\Mistral\Dto\Files;

use Spatie\LaravelData\Data;

final class UploadFileOut extends Data
{
    public function __construct(
        public FileObject $data,
    ) {}
}
