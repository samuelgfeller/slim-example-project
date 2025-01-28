<?php

namespace App\Module\Dashboard\TogglePanel\Service;

use App\Domain\Validation\RequestBodyKeyValidator;
use App\Module\FilterSetting\Enum\FilterModule;
use App\Module\FilterSetting\Save\Service\FilterSettingSaver;

/**
 * Class that enables or disables a panel in the dashboard.
 */
final readonly class ActiveDashboardPanelChanger
{
    public function __construct(
        private FilterSettingSaver $filterSettingSaver,
        private RequestBodyKeyValidator $requestBodyKeyValidator,
    ) {
    }

    public function toggleDashboardPanel(array $params)
    {
        // As there is no other validation, the request body is checked for valid keys here
        if (!$this->requestBodyKeyValidator->requestBodyHasValidKeys($params, ['panelIds'])) {
            throw new \InvalidArgumentException('Invalid request body keys.');
        }
        $this->filterSettingSaver->saveFilterSettingForAuthenticatedUser(
            json_decode($params['panelIds'], true, 512, JSON_UNESCAPED_SLASHES | JSON_PARTIAL_OUTPUT_ON_ERROR),
            FilterModule::DASHBOARD_PANEL
        );
    }

}