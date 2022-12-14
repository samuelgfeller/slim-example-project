<?php

/**
 * @var int $authenticatedUserId
 * @var \App\Domain\Dashboard\Data\DashboardData[] $dashboards
 * @var array $enabledDashboards dashboard ids of enabled dashboards
 */

$this->setLayout('layout.html.php');
$this->addAttribute('css', [
    'assets/general/page-component/content-placeholder/content-placeholder.css',
    'assets/client/list/client-list-loading-placeholder.css',
    'assets/client/list/client-list.css', // For clients
    'assets/general/page-component/contenteditable/contenteditable.css', // For notes
    'assets/general/page-component/loader/animated-checkmark.css', // Note loader
    'assets/client/note/client-note.css', // Note css
    'assets/general/page-component/filter-chip/filter-chip.css', // User
    'assets/user/list/user-list-content-placeholder.css', // User
    'assets/user/list/user-list.css', // User
    'assets/general/page-component/panel/panel.css',
    'assets/dashboard/dashboard.css',
]);
$this->addAttribute('jsModules', [
    'assets/dashboard/dashboard-main.js',
]);
?>

<h1>Dashboard</h1>

<div id="dashboard-panel-toggle-buttons-div">
    <?php
    foreach ($dashboards as $dashboard) {
        $checked = in_array($dashboard->panelId, $enabledDashboards, true) ? 'checked' : '';
        echo "<label class='checkbox-label dashboard-panel-toggle-btn' data-panel-id='$dashboard->panelId'>
                <input type='checkbox' $checked><span>$dashboard->title</span>
              </label>";
    }
    ?>
</div>
<div class="dashboard-panel-container">
    <?php
    foreach ($dashboards as $dashboard) { ?>
        <div class="panel-container <?= $dashboard->panelClass ?>" id="<?= $dashboard->panelId ?>">
            <div class="panel-header">
                <h2><?= $dashboard->title ?></h2>
                <img class="toggle-panel-icon" src="assets/general/general-img/action/arrow-icon.svg"
                     alt="toggle-open-close">
            </div>
            <div class="panel-content">
                <?= $dashboard->panelHtmlContent ?>
            </div>
        </div>
        <?php
    } ?>
</div>
