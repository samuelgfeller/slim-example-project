<?php

/**
 * @var int $authenticatedUserId
 * @var array $statuses with as key the name and value the id
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

    <div class="panel-container client-panel">
        <div class="panel-header">
            <h2>Unassigned clients</h2>
        </div>
        <div class="panel-content">
            <data data-param-name="user" data-param-value="" value=""></data>
            <div id="client-wrapper-unassigned" class="client-wrapper"></div>
        </div>
    </div>
    <div class="panel-container client-panel">
        <div class="panel-header">
            <h2>Clients assigned to me - action pending</h2>
        </div>
        <div class="panel-content">
            <data data-param-name="user" data-param-value="<?= $authenticatedUserId ?>" value=""></data>
            <data data-param-name="status" data-param-value="<?= $statuses['Action pending'] ?>" value=""></data>
            <div id="client-wrapper-assigned-to-me" class="client-wrapper"></div>
        </div>
    </div>
    <div class="panel-container">
        <div class="panel-header">
            <h2>User activity</h2>
        </div>
        <div class="panel-content">
            aaäslfjöylkdjfma
        </div>
    </div>
</div>
