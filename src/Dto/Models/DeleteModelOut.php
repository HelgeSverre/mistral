<?php

namespace HelgeSverre\Mistral\Dto\Models;

use Spatie\LaravelData\Data as SpatieData;

/**
 * Delete Model Response
 */
class DeleteModelOut extends SpatieData
{
    public function __construct(
        public string $id,
        public string $object = 'model',
        public bool $deleted = true,
    ) {}
}
