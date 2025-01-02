<?php

namespace App\Modules\User\Enum;

use App\Core\Shared\Trait\EnumToArray;

enum UserTheme: string
{
    use EnumToArray;

    case light = 'light';
    case dark = 'dark';
}
