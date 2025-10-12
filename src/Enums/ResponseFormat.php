<?php

namespace HelgeSverre\Mistral\Enums;

enum ResponseFormat: string
{
    case JSON = 'json';
    case TEXT = 'text';
    case VERBOSE_JSON = 'verbose_json';
}
