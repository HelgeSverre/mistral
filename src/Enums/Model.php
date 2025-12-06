<?php

namespace HelgeSverre\Mistral\Enums;

enum Model: string
{
    // General purpose models
    case mistral7b = 'open-mistral-7b';
    case mixtral = 'open-mixtral-8x7b';
    case small = 'mistral-small-latest';
    case medium = 'mistral-medium-latest';
    case large = 'mistral-large-latest';

    // Edge models
    case ministral3b = 'ministral-3b-latest';
    case ministral8b = 'ministral-8b-latest';

    // Nemo models
    case mistralNemo = 'open-mistral-nemo';

    // Reasoning models
    case magistralMedium = 'magistral-medium-latest';

    // Coding models
    case codestral = 'codestral-latest';

    // Vision models
    case pixtralLarge = 'pixtral-large-latest';
    case pixtral12b = 'pixtral-12b-latest';

    // Audio models
    case voxtralSmall = 'voxtral-small-latest';

    // Embedding models
    case embed = 'mistral-embed';

    // Deprecated models (kept for backward compatibility)
    case oldMedium = 'mistral-medium-2312';
    case oldSmall = 'mistral-small-2312';
    case oldTiny = 'mistral-tiny-2312';

    public static function withJsonModeSupport(): array
    {
        return [
            // Small
            'mistral-small-latest',
            'mistral-small-2402',

            // Large
            'mistral-large-latest',
            'mistral-large-2402',

            // Ministral (edge)
            'ministral-3b-latest',
            'ministral-8b-latest',

            // Nemo
            'open-mistral-nemo',

            // Reasoning
            'magistral-medium-latest',

            // Vision
            'pixtral-large-latest',
            'pixtral-12b-latest',
        ];
    }
}
