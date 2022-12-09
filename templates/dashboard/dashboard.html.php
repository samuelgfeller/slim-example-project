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

    <!--Unassigned-->
    <!--<div class="panel-container client-panel" id="unassigned-panel">-->
    <!--    <div class="panel-header">-->
    <!--        <h2>Unassigned clients</h2>-->
    <!--        <img class="toggle-panel-icon" src="assets/general/img/action/arrow-icon.svg" alt="toggle-open-close">-->
    <!--    </div>-->
    <!--    <div class="panel-content">-->
    <!--        <data data-param-name="user" data-param-value="" value=""></data>-->
    <!--        <div id="client-wrapper-unassigned" class="client-wrapper"></div>-->
    <!--    </div>-->
    <!--</div>-->
    <!--<!--Assigned to me - action pending-->-->
    <!--<div class="panel-container client-panel" id="assigned-to-me-panel">-->
    <!--    <div class="panel-header">-->
    <!--        <h2>Clients assigned to me &nbsp; — &nbsp; action pending</h2>-->
    <!--        <img class="toggle-panel-icon" src="assets/general/img/action/arrow-icon.svg" alt="toggle-open-close">-->
    <!--    </div>-->
    <!--    <div class="panel-content">-->
    <!--        <data data-param-name="user" data-param-value="--><?
    //= $authenticatedUserId ?><!--" value=""></data>-->
    <!--        <data data-param-name="status" data-param-value="--><?
    //= $statuses['Action pending'] ?><!--" value=""></data>-->
    <!--        <div id="client-wrapper-assigned-to-me" class="client-wrapper"></div>-->
    <!--    </div>-->
    <!--</div>-->
    <!---->
    <!--<!--Recently assigned - managing advisor-->-->
    <!--<div class="panel-container" id="recently-assigned-panel">-->
    <!--    <div class="panel-header">-->
    <!--        <h2>Recently assigned clients</h2>-->
    <!--        <img class="toggle-panel-icon" src="assets/general/img/action/arrow-icon.svg" alt="toggle-open-close">-->
    <!--    </div>-->
    <!--    <div class="panel-content">-->
    <!--        <p>-->
    <!--        </p>-->
    <!--    </div>-->
    <!--</div>-->
    <!---->
    <!--<!--New notes activity - managing advisor-->-->
    <!--<div class="panel-container" id="new-notes-panel">-->
    <!--    <div class="panel-header">-->
    <!--        <h2>New notes</h2>-->
    <!--        <img class="toggle-panel-icon" src="assets/general/img/action/arrow-icon.svg" alt="toggle-open-close">-->
    <!--    </div>-->
    <!--    <div class="panel-content">-->
    <!--        <p>-->
    <!--        </p>-->
    <!--    </div>-->
    <!--</div>-->
    <!---->
    <!--<!--Specific user activity - managing advisor-->-->
    <!--<div class="panel-container" id="user-activity-panel">-->
    <!--    <div class="panel-header">-->
    <!--        <h2>User activity</h2>-->
    <!--        <img class="toggle-panel-icon" src="assets/general/img/action/arrow-icon.svg" alt="toggle-open-close">-->
    <!--    </div>-->
    <!--    <div class="panel-content">-->
    <!--        <p>-->
    <!--        </p>-->
    <!--    </div>-->
    <!--</div>-->
</div>
