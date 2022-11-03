<?php
/**
 * @var \Slim\Interfaces\RouteParserInterface $route
 * @var $this \Slim\Views\PhpRenderer Rendering engine
 * @var $clientAggregate \App\Domain\Client\Data\ClientResultAggregateData client
 * @var $dropdownValues App\Domain\Client\Data\ClientDropdownValuesData all statuses, users and sexes to populate dropdown
 */


use App\Domain\Authorization\Privilege;

$this->setLayout('layout.html.php');
?>

<?php
// Define assets that should be included
// Populate variable $css for layout which then generates the HTML code to include assets
$this->addAttribute('css', [
    'assets/general/css/form.css',
    'assets/general/css/modal/alert-modal.css',
    'assets/general/css/loader/animated-checkmark.css',
    'assets/general/css/plus-button.css',
    'assets/general/css/content-placeholder.css',
    // page specific css has to come last to overwrite other styles
    'assets/client/client-read.css'
]);
$this->addAttribute('js', []);
// Js files that import things from other js files
$this->addAttribute('jsModules', ['assets/client/js/read/client-read-main.js']);
?>
<!-- Store client id on the page for js to read it -->
<data id="client-id" value="<?= $clientAggregate->id ?>"></data>

<div id="title-and-dropdown-flexbox">
    <div id="full-header-edit-icon-container">
        <div class="partial-header-edit-icon-div" data-field-element="h1">
            <!-- Img has to be before title because we are only able to style next sibling in css -->
            <img src="assets/general/img/material-edit-icon.svg" class="contenteditable-edit-icon cursor-pointer"
                 alt="Edit"
                 id="edit-first-name-btn">
            <h1 data-name="first_name"><?= html($clientAggregate->firstName) ?></h1>
        </div>
        <div class="partial-header-edit-icon-div" data-field-element="h1">
            <img src="assets/general/img/material-edit-icon.svg" class="contenteditable-edit-icon cursor-pointer"
                 alt="Edit"
                 id="edit-last-name-btn">
            <h1 data-name="last_name"> <?= html($clientAggregate->lastName) ?></h1>
        </div>
    </div>
    <!-- Status and assigned user select options containers -->
    <div id="status-and-assigned-user-select-container">
        <!-- Status select options-->
        <div>
            <label for="client-status" class="discrete-label">Status</label>
            <select name="client_status_id" class="default-select"
                <?= $clientAggregate->clientStatusPrivilege->hasPrivilege(Privilege::UPDATE)
                    ? '' : 'disabled' ?>>
                <?php
                // Client status select options
                foreach ($dropdownValues->statuses as $statusId => $statusName) {
                    $selected = $statusId === $clientAggregate->clientStatusId ? 'selected' : '';
                    echo "<option value='$statusId' $selected>$statusName</option>";
                }
                ?>
            </select>
        </div>

        <!-- Assigned user select options-->
        <div>
            <label for="assigned-user-select" class="discrete-label">Helper</label>
            <select name="user_id" class="default-select" id="assigned-user-select"
                <?= $clientAggregate->assignedUserPrivilege->hasPrivilege(Privilege::UPDATE) ? '' : 'disabled' ?>>
                <?php
                // Linked user select options
                foreach ($dropdownValues->users as $id => $name) {
                    $selected = $id === $clientAggregate->userId ? 'selected' : '';
                    echo "<option value='$id' $selected>$name</option>";
                }
                ?>
            </select>
        </div>
    </div>
</div>

<div class="main-note-div">
    <div id="main-note-textarea-div">
        <textarea name="message" class="auto-resize-textarea main-textarea"
                  minlength="0" maxlength="500"
                  data-editable="<?= $clientAggregate->mainNoteData
                      ->privilege->hasPrivilege(Privilege::UPDATE) ? '1' : '0' ?>"
                  data-note-id="<?= $clientAggregate->mainNoteData->id ?? 'new-main-note' ?>"
                  placeholder="New main note"
        ><?= html($clientAggregate->mainNoteData->message) ?></textarea>
        <div class="circle-loader client-read">
            <div class="checkmark draw"></div>
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

    <?php
    if ($clientAggregate->location || $clientAggregate->phone || $clientAggregate->email) { ?>
        <div id="client-personal-info-flex-container">
            <?php
            if ($clientAggregate->location) { ?>
                <a href="https://www.google.ch/maps/search/<?= $clientAggregate->location ?>" target="_blank">
                    <img src="assets/client/img/location_pin_icon.svg" class="default-icon" alt="location">
                    <div class="partial-personal-info-and-edit-icon-div" data-field-element="a-span">
                        <img src="assets/general/img/material-edit-icon.svg"
                             class="contenteditable-edit-icon cursor-pointer" alt="Edit"
                             id="edit-location-btn">
                        <span data-name="location"><?= $clientAggregate->location ?></span>
                    </div>
                </a>
                <?php
            }
            if ($clientAggregate->phone) { ?>
                <a href="tel:<?= $clientAggregate->phone ?>" target="_blank">
                    <img src="assets/client/img/phone.svg" class="profile-card-content-icon" alt="phone">
                    <div class="partial-personal-info-and-edit-icon-div" data-field-element="a-span">
                        <img src="assets/general/img/material-edit-icon.svg"
                             class="contenteditable-edit-icon cursor-pointer" alt="Edit"
                             id="edit-phone-btn">
                        <span data-name="phone"><?= $clientAggregate->phone ?></span>
                    </div>
                </a>
                <?php
            }
            if ($clientAggregate->email) { ?>
                <a href="mailto:<?= $clientAggregate->email ?>" target="_blank">
                    <img src="assets/client/img/email-icon.svg" class="profile-card-content-icon" alt="phone">
                    <?php
                    $emailParts = explode('@', $clientAggregate->email);
                    ?>
                    <div id="email-div" class="partial-personal-info-and-edit-icon-div" data-field-element="a-span">
                        <img src="assets/general/img/material-edit-icon.svg"
                             class="contenteditable-edit-icon cursor-pointer" alt="Edit"
                             id="edit-email-btn">
                        <span id="email-prefix" data-name="email"><?= $emailParts[0] ?><br>@<?= $emailParts[1] ?></span>
                        <!--<span id="email-prefix">--><?php //= $emailParts[0] ?><!--</span><br><span-->
                                <!--id="email-suffix">@--><?php //= $emailParts[1] ?><!--</span>-->

                    </div>
                </a>
                <?php
            } ?>
        </div>
        <?php
    } ?>
</div>