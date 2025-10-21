<?php

namespace HelgeSverre\Mistral\Dto\Files;

use Spatie\LaravelData\Data;

final class DeleteFileOut extends Data
{
    public function __construct(
        public string $id,
        public string $object,
        public bool $deleted,
    ) {}
}
