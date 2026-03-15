<?php

namespace App\Enums;

enum TransitionType: string
{
    case ONE_TO_ONE = '1_TO_1';
    case ONE_TO_MANY = '1_TO_N';
    case MANY_TO_ONE = 'N_TO_1';
}

