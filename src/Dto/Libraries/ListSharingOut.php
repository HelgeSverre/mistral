<?php

declare(strict_types=1);

namespace HelgeSverre\Mistral\Dto\Libraries;

use Spatie\LaravelData\Attributes\DataCollectionOf;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\DataCollection;

class ListSharingOut extends Data
{
    /**
     * @param  DataCollection<int, SharingOut>  $data
     */
    public function __construct(
        #[DataCollectionOf(SharingOut::class)]
        public DataCollection $data,
        public ?string $object = null,
    ) {}
}
