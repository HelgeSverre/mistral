<?php

namespace HelgeSverre\Mistral\Enums;

enum SampleType: string
{
    case PRETRAIN = 'pretrain';
    case INSTRUCT = 'instruct';
    case BATCH_REQUEST = 'batch_request';
}
