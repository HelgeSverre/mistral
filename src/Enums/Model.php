<?php

namespace HelgeSverre\Mistral\Enums;

enum Model: string
{
    // New model names
    case mistral7b = 'open-mistral-7b';
    case mixtral = 'open-mixtral-8x7b';
    case small = 'mistral-small-latest';
    case medium = 'mistral-medium-latest';
    case large = 'mistral-large-latest';

    // Old model names
    case oldMedium = 'mistral-medium-2312';
    case oldSmall = 'mistral-small-2312';
    case oldTiny = 'mistral-tiny-2312';

    case embed = 'mistral-embed';

    public static function withJsonModeSupport(): array
    {
        return [
            // Small
            'mistral-small-latest',
            'mistral-small-2402',

            // Large
            'mistral-large-latest',
            'mistral-large-2402',
        ];
    }
}
