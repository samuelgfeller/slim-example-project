<?php

namespace App\Domain\UserFilterSetting;

enum UserFilterModule: string
{
    case CLIENT_LIST = 'client-list';
    case DASHBOARD_USER_ACTIVITY = 'dashboard-user-activity';
}
