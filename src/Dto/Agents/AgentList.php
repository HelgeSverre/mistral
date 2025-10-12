<?php

namespace HelgeSverre\Mistral\Dto\Agents;

use Spatie\LaravelData\Attributes\DataCollectionOf;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\DataCollection;

class AgentList extends Data
{
    public function __construct(
        public string $object,
        #[DataCollectionOf(Agent::class)]
        public DataCollection $data,
        public ?int $total = null,
    ) {}
}
