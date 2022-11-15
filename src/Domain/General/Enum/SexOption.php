<?php

namespace App\Domain\General\Enum;

use App\Domain\Utility\Trait\EnumToArray;

enum SexOption : string
{
    use EnumToArray;

    // First letter uppercase and rest lowercase as names are used as labels in html form
    case Male = 'M';
    case Female = 'F';
    case Other = 'O';
    // Cannot have null as it is displayed in the create form as radio buttons
    // case NULL = '';
}
