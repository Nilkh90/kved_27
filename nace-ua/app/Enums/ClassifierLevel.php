<?php

namespace App\Enums;

enum ClassifierLevel: string
{
    case SECTION = 'SECTION';
    case DIVISION = 'DIVISION';
    case GROUP = 'GROUP';
    case CLASS = 'CLASS';
    case SUBCLASS = 'SUBCLASS';
}

