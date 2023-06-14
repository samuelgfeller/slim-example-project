<?php

namespace App\Domain\User\Enum;

use App\Common\Trait\EnumToArray;

enum UserLang: string
{
    use EnumToArray;
    // Case names are used as label names for the radio buttons hence the upper case first letter
    // It isn't ideal however as only ASCII chars are allowed and "Français" already has a non-ASCII char,
    // so it would probably be a lot better if name and value was switched BUT unfortunately PHP does not
    // currently have a neat option I know of to "get" the enum by the name like tryFrom() does with the value
    // and that is needed in the data object constructor to populate the instance variable with the real enum case.
    // This is to be improved when there is time for it.
    case English = 'en_US';
    case Deutsch = 'de_CH';
    case Francais = 'fr_CH';
}
