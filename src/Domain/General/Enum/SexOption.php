<?php

namespace App\Domain\General\Enum;

use App\Common\Trait\EnumToArray;

enum SexOption: string
{
    use EnumToArray;

    case MALE = 'M';
    case FEMALE = 'F';
    case OTHER = 'O';
    // Cannot have null as it is displayed in the create form as radio buttons
    // case NULL = '';
}
