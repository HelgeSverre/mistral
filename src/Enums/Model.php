<?php

namespace HelgeSverre\Mistral\Enums;

enum Model: string
{
    case medium = 'mistral-medium';
    case small = 'mistral-small';
    case tiny = 'mistral-tiny';

    case embed = 'mistral-embed';
}
