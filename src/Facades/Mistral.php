<?php

namespace HelgeSverre\Mistral\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @see \HelgeSverre\Mistral\Mistral
 */
class Mistral extends Facade
{
    protected static function getFacadeAccessor()
    {
        return \HelgeSverre\Mistral\Mistral::class;
    }
}
