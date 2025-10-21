<?php

declare(strict_types=1);

namespace HelgeSverre\Mistral\Enums;

enum AccessRole: string
{
    case OWNER = 'owner';
    case EDITOR = 'editor';
    case VIEWER = 'viewer';
}
