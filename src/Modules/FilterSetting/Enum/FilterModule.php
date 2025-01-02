<?php

namespace App\Modules\FilterSetting\Enum;

enum FilterModule: string
{
    case CLIENT_LIST = 'client-list';
    case DASHBOARD_USER_ACTIVITY = 'dashboard-user-activity';
    case DASHBOARD_PANEL = 'dashboard-panel';
}
