<?php

/**
 * @var int $authenticatedUserId
 * @var \App\Domain\Dashboard\Data\DashboardData[] $dashboards
 */

$this->setLayout('layout.html.php');
$this->addAttribute('css', [
    'assets/general/css/content-placeholder.css',
    'assets/client/list/client-list-loading-placeholder.css',
    'assets/client/list/client-list.css',
    'assets/general/panel/panel.css',
    'assets/dashboard/dashboard.css',
]);
$this->addAttribute('jsModules', [
    'assets/dashboard/dashboard-main.js',
]);
?>

<h1>Dashboard</h1>

<div class="dashboard-panel-container">

    <?php
    foreach ($dashboards as $dashboard) {
        if ($dashboard->authorized) { ?>
            <div class="panel-container <?= $dashboard->panelClass ?>" id="<?= $dashboard->panelId ?>">
                <div class="panel-header">
                    <h2><?= $dashboard->title ?></h2>
                    <img class="toggle-panel-icon" src="assets/general/img/action/arrow-icon.svg"
                         alt="toggle-open-close">
                </div>
                <div class="panel-content">
                    <?= $dashboard->panelHtmlContent ?>
                </div>
            </div>
            <?php
        }
    } ?>
</div>
