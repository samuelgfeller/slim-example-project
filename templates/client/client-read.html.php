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
    'assets/general/css/contenteditable.css',
    // page specific css has to come last to overwrite other styles
    'assets/client/client-read.css'
]);
$this->addAttribute('js', []);
// Js files that import things from other js files
$this->addAttribute('jsModules', ['assets/client/js/read/client-read-main.js']);

// Store client id on the page in <data> element for js to read it
?>
<data id="client-id" value="<?= $clientAggregate->id ?>"></data>

<div id="title-and-dropdown-flexbox">
    <div id="full-header-edit-icon-container">
        <div class="partial-header-edit-icon-div contenteditable-field-container" data-field-element="h1">
            <?php
            if ($clientAggregate->mainDataPrivilege->hasPrivilege(Privilege::UPDATE)) { ?>
                <!-- Img has to be before title because we are only able to style next sibling in css -->
                <img src="assets/general/img/material-edit-icon.svg" class="contenteditable-edit-icon cursor-pointer"
                     alt="Edit"
                     id="edit-first-name-btn">
                <?php
            } ?>
            <h1 data-name="first_name" data-minlength="2" data-maxlength="100" spellcheck="false"><?=
                !empty($clientAggregate->firstName) ? html($clientAggregate->firstName) : '&nbsp;' ?></h1>
        </div>
        <div class="partial-header-edit-icon-div contenteditable-field-container" data-field-element="h1">
            <?php
            if ($clientAggregate->mainDataPrivilege->hasPrivilege(Privilege::UPDATE)) { ?>
                <img src="assets/general/img/material-edit-icon.svg" class="contenteditable-edit-icon cursor-pointer"
                     alt="Edit"
                     id="edit-last-name-btn">
                <?php
            } ?>
            <h1 data-name="last_name" data-minlength="2" data-maxlength="100" spellcheck="false"><?=
                !empty($clientAggregate->lastName) ? html($clientAggregate->lastName) : '&nbsp;' ?></h1>
        </div>
    </div>
    <!-- Status and assigned user select options containers -->
    <div id="status-and-assigned-user-select-container">
        <!-- Status select options-->
        <div>
            <label for="client-status" class="bigger-select-label">Status</label>
            <select name="client_status_id" class="default-select bigger-select"
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
            <label for="assigned-user-select" class="bigger-select-label">Helper</label>
            <select name="user_id" class="default-select bigger-select" id="assigned-user-select"
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

<div id="main-note-div">
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
    <div id="client-personal-info-container">
        <div id="client-personal-info-flex-container">
            <img src="assets/general/img/material-edit-icon.svg"
                 class="contenteditable-edit-icon cursor-pointer" alt="Edit"
                 id="toggle-personal-info-edit-icons">
            <!-- id prefix has to be the same as alt attr of personal-info-icon inside here but also available icons -->
            <div id="birthdate-container" style="<?= $clientAggregate->birthdate ? '' : 'display: none;' ?>">
                <img src="assets/general/img/birthdate-icon.svg" class="personal-info-icon" alt="birthdate">
                <div class="partial-personal-info-and-edit-icon-div contenteditable-field-container" data-field-element="span"
                     data-hide-if-empty="true">
                    <?php
                    if ($clientAggregate->mainDataPrivilege->hasPrivilege(Privilege::UPDATE)) { ?>
                        <img src="assets/general/img/material-edit-icon.svg"
                             class="contenteditable-edit-icon cursor-pointer" alt="Edit"
                             id="edit-birthdate-btn">
                        <?php
                    } ?>
                    <span spellcheck="false" data-name="birthdate" data-maxlength="254"
                          class="contenteditable-placeholder" data-placeholder="dd.mm.yyyy"
                    ><?php
                        if ($clientAggregate->birthdate) {
                            echo $clientAggregate->birthdate->format('d.m.Y') ?><span id="age-sub-span"
                            >&nbsp; • &nbsp;<?= (new DateTime())->diff(
                            $clientAggregate->birthdate
                        )->y ?></span><?php
                        } else {
                            echo '&nbsp;';
                        } ?></span>
                </div>
            </div>
            <div id="sex-container" style="<?= $clientAggregate->sex ? '' : 'display: none;' ?>">
                <img src="assets/general/img/gender-icon.svg" class="personal-info-icon" alt="sex">
                <div class="partial-personal-info-and-edit-icon-div contenteditable-field-container" data-field-element="select"
                     data-hide-if-empty="true">
                    <?php
                    if ($clientAggregate->mainDataPrivilege->hasPrivilege(Privilege::UPDATE)) { ?>
                        <img src="assets/general/img/material-edit-icon.svg"
                             class="contenteditable-edit-icon cursor-pointer" alt="Edit"
                             id="edit-sex-btn">
                        <select name="sex" class="default-select" id="sex-select">
                            <option value=""></option>
                            <?php
                            // Linked user select options
                            foreach ($dropdownValues->sexes as $id => $name) {
                                $selected = $id === $clientAggregate->sex ? 'selected' : '';
                                echo "<option value='$id' $selected>$name</option>";
                            }
                            ?>
                        </select>
                        <?php
                    } ?>

                    <span spellcheck="false" data-name="sex" data-maxlength="254"
                    ><?= $clientAggregate->sex ? $dropdownValues->sexes[$clientAggregate->sex] : '' ?></span>
                </div>
            </div>
            <a href="https://www.google.ch/maps/search/<?= $clientAggregate->location ?>" target="_blank"
               id="location-container" style="<?= $clientAggregate->location ? '' : 'display: none;' ?>">
                <img src="assets/client/img/location_pin_icon.svg" class="personal-info-icon" alt="location">
                <div class="partial-personal-info-and-edit-icon-div contenteditable-field-container" data-field-element="a-span"
                     data-hide-if-empty="true">
                    <?php
                    if ($clientAggregate->mainDataPrivilege->hasPrivilege(Privilege::UPDATE)) { ?>
                        <img src="assets/general/img/material-edit-icon.svg"
                             class="contenteditable-edit-icon cursor-pointer" alt="Edit"
                             id="edit-location-btn">
                        <?php
                    } ?>
                    <span data-name="location" data-minlength="2" data-maxlength="100" spellcheck="false"><?=
                        html($clientAggregate->location) ?></span>
                </div>
            </a>
            <a href="tel:<?= $clientAggregate->phone ?>" target="_blank"
               id="phone-container" style="<?= $clientAggregate->phone ? '' : 'display: none;' ?>">
                <img src="assets/client/img/phone.svg" class="personal-info-icon" alt="phone">
                <div class="partial-personal-info-and-edit-icon-div contenteditable-field-container" data-field-element="a-span"
                     data-hide-if-empty="true">
                    <?php
                    if ($clientAggregate->mainDataPrivilege->hasPrivilege(Privilege::UPDATE)) { ?>
                        <img src="assets/general/img/material-edit-icon.svg"
                             class="contenteditable-edit-icon cursor-pointer" alt="Edit"
                             id="edit-phone-btn">
                        <?php
                    } ?>
                    <span data-name="phone" data-minlength="3" data-maxlength="20" spellcheck="false"><?=
                        html($clientAggregate->phone) ?></span>
                </div>
            </a>
            <a href="mailto:<?= $clientAggregate->email ?>" target="_blank"
               id="email-container" style="<?= $clientAggregate->email ? '' : 'display: none;' ?>">
                <img src="../../public/assets/general/img/personal-data-icons/email-icon.svg" class="personal-info-icon" alt="email">
                <div id="email-div" class="partial-personal-info-and-edit-icon-div contenteditable-field-container" data-field-element="a-span"
                     data-hide-if-empty="true">
                    <?php
                    if ($clientAggregate->mainDataPrivilege->hasPrivilege(Privilege::UPDATE)) { ?>
                        <img src="assets/general/img/material-edit-icon.svg"
                             class="contenteditable-edit-icon cursor-pointer" alt="Edit"
                             id="edit-email-btn">
                        <?php
                    } ?>
                    <?php
                    $emailParts = $clientAggregate->email ? explode('@', $clientAggregate->email) : ['', '']; ?>
                    <span id="email-prefix" spellcheck="false" data-name="email" data-maxlength="254"
                    ><?= html($emailParts[0]) ?><br>@<?= html($emailParts[1]) ?></span>
                </div>
            </a>
        </div>

        <div id="add-client-personal-info-div">
            <img src="assets/general/img/plus-icon.svg" id="toggle-personal-info-icons" alt="add info">
            <!-- alt has to be the same as the field name -->
            <img src="assets/general/img/birthdate-icon.svg" class="personal-info-icon" alt="birthdate">
            <img src="assets/general/img/gender-icon.svg" class="personal-info-icon" alt="sex">
            <img src="assets/client/img/location_pin_icon.svg" class="personal-info-icon" alt="location">
            <img src="assets/client/img/phone.svg" class="personal-info-icon" alt="phone">
            <img src="../../public/assets/general/img/personal-data-icons/email-icon.svg" class="personal-info-icon" alt="email">
        </div>
    </div>
</div>