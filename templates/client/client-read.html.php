<?php
/**
 * @var \Slim\Interfaces\RouteParserInterface $route
 * @var $this \Slim\Views\PhpRenderer Rendering engine
 * @var $clientAggregate \App\Domain\Client\Data\ClientResultAggregateData client
 * @var $dropdownValues App\Domain\Client\Data\ClientDropdownValuesData all statuses, users and sexes to populate dropdown
 */


$this->setLayout('layout.html.php');
?>

<?php
// Define assets that should be included
// Populate variable $css for layout which then generates the HTML code to include assets
$this->addAttribute('css', [
    'assets/general/css/form.css',
    'assets/general/css/alert-modal.css',
    'assets/general/css/loader/animated-checkmark.css',
    'assets/general/css/plus-button.css',
    'assets/general/css/content-placeholder.css',
    'assets/general/css/client-list-loading-placeholder.css',
    // page specific css has to come last to overwrite other styles
    'assets/client/client-read.css'
]);
$this->addAttribute('js', []);
// Js files that import things from other js files
$this->addAttribute('jsModules', ['assets/client/js/read/client-read-main.js']);
?>
<!-- Store client id on the page for js to read it -->
<data id="client-id" value="<?= $clientAggregate->id ?>"></data>

<h1><?= html($clientAggregate->first_name . ' ' . $clientAggregate->last_name) ?></h1>

<div class="main-note-status-assigned-user-div">
    <div id="main-note-textarea-div">
        <textarea name="message" class="auto-resize-textarea main-textarea" data-editable="1"
                  data-note-id="<?= $clientAggregate->mainNoteData->id ?? 'new-main-note' ?>"
        ><?= html($clientAggregate->mainNoteData->message) ?></textarea>
        <div class="circle-loader client-read">
            <div class="checkmark draw"></div>
        </div>
    </div>
    <!-- Status and assigned user select options containers -->
    <div id="status-and-assigned-user-select-containers">
        <!-- Status select options-->
        <div>
            <label for="client-status" class="discrete-label">Status</label>
            <select name="client_status_id" class="default-select">
                <?php
                // Client status select options
                foreach ($dropdownValues->statuses as $statusId => $statusName) {
                    $selected = $statusId === $clientAggregate->client_status_id ? 'selected' : '';
                    echo "<option value='$statusId' $selected>$statusName</option>";
                }
                ?>
            </select>
        </div>

        <!-- Assigned user select options-->
        <div>
            <label for="assigned-user" class="discrete-label">Helper</label>
            <select name="user_id" class="default-select" id="assigned-user">
                <?php
                // Client status select options
                foreach ($dropdownValues->users as $id => $name) {
                    $selected = $id === $clientAggregate->user_id ? 'selected' : '';
                    echo "<option value='$id' $selected>$name</option>";
                }
                ?>
            </select>
        </div>
    </div>
</div>

<div id="client-activity-personal-info-container">

    <div id="client-activity-textarea-container" data-notes-amount="<?= $clientAggregate->notesAmount ?>">
        <div class="vertical-center" id="activity-header">
            <h2>Aktivität</h2>
            <div class="plus-btn" id="create-note-btn"></div>
        </div>
        <!--  Notes are populated here via ajax  -->
    </div>

    <div id="client-personal-info-flex-container">

        <?php
        if ($clientAggregate->location) { ?>
            <a href="https://www.google.ch/maps/search/<?= $clientAggregate->location ?>" target="_blank">
                <img src="assets/client/img/location_pin_icon.svg" class="default-icon" alt="location">
                <span><?= $clientAggregate->location ?></span>
            </a>
            <?php
        }
        if ($clientAggregate->phone) { ?>
            <a href="tel:<?= $clientAggregate->phone ?>" target="_blank">
                <img src="assets/client/img/phone.svg" class="profile-card-content-icon" alt="phone">
                <span><?= $clientAggregate->phone ?></span>
            </a>
            <?php
        }
        if ($clientAggregate->email) { ?>
            <a href="mailto:<?= $clientAggregate->email ?>" target="_blank">
                <img src="assets/client/img/email-icon.svg" class="profile-card-content-icon" alt="phone">
                <?php
                $emailParts = explode('@', $clientAggregate->email);
                ?>
                <div id="email-div">
                <span id="email-prefix"><?= $emailParts[0] ?></span><br><span id="email-suffix">@<?= $emailParts[1] ?></span>
                </div>
            </a>
            <?php
        } ?>
    </div>

</div>