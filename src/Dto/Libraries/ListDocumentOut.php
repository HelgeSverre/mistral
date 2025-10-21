<?php

declare(strict_types=1);

namespace HelgeSverre\Mistral\Dto\Libraries;

use Spatie\LaravelData\Attributes\DataCollectionOf;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\DataCollection;

class ListDocumentOut extends Data
{
    /**
     * @param  DataCollection<int, DocumentOut>  $data
     */
    public function __construct(
        #[DataCollectionOf(DocumentOut::class)]
        public DataCollection $data,
        public ?string $object = null,
        public ?int $total = null,
    ) {}
}
