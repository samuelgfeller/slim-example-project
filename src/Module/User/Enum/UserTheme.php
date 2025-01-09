<?php

namespace App\Module\User\Enum;

use App\Core\Common\Trait\EnumToArray;

enum UserTheme: string
{
    use EnumToArray;

    case light = 'light';
    case dark = 'dark';
}
