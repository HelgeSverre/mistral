<?php

namespace HelgeSverre\Mistral\Dto\Batch;

use Spatie\LaravelData\Attributes\DataCollectionOf;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\DataCollection;

class BatchJobsOut extends Data
{
    /**
     * @param  DataCollection<int, BatchJobOut>  $data
     */
    public function __construct(
        #[DataCollectionOf(BatchJobOut::class)]
        public DataCollection $data,
        public int $total,
        public string $object = 'list',
    ) {}
}
