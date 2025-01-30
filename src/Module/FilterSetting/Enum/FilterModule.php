<?php

namespace App\Module\FilterSetting\Enum;

enum FilterModule: string
{
    case CLIENT_LIST = 'client-list';
    case DASHBOARD_USER_ACTIVITY = 'dashboard-user-activity';
    case DASHBOARD_PANEL = 'dashboard-panel';
}
