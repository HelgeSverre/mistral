<?php

namespace HelgeSverre\Mistral\Enums;

enum Role: string
{
    case system = 'system';
    case assistant = 'assistant';
    case user = 'user';
}
