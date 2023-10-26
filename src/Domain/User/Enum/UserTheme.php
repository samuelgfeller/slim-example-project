<?php

namespace App\Domain\User\Enum;

use App\Common\Trait\EnumToArray;

enum UserTheme: string
{
    use EnumToArray;

    case light = 'light';
    case dark = 'dark';
}
