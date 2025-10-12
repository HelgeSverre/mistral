<?php

namespace HelgeSverre\Mistral\Dto\Files;

use Spatie\LaravelData\Attributes\DataCollectionOf;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\DataCollection;

final class ListFilesOut extends Data
{
    public function __construct(
        public string $object,
        #[DataCollectionOf(FileObject::class)]
        public DataCollection $data,
        public int $total,
    ) {}
}
