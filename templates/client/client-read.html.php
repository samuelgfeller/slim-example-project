<?php
/**
 * @var \Slim\Interfaces\RouteParserInterface $route
 * @var \Slim\Views\PhpRenderer $this Rendering engine
 * @var \App\Domain\Client\Data\ClientReadResult $clientReadData client
 * @var App\Domain\Client\Data\ClientDropdownValuesData $dropdownValues all statuses, users and sexes to populate dropdown
 */

$this->setLayout('layout/layout.html.php');
?>

<?php
// Define assets that should be included
// Populate variable $css for layout which then generates the HTML code to include assets
$this->addAttribute('css', [
    'assets/general/page-component/form/form.css',
    'assets/general/page-component/modal/alert-modal.css',
    'assets/general/page-component/loader/animated-checkmark.css',
    'assets/general/page-component/button/plus-button.css',
    'assets/general/page-component/skeleton-loader/skeleton-loader.css',
    'assets/client/read/client-read-note-skeleton-loader.css',
    'assets/general/page-component/contenteditable/contenteditable.css',
    // page-specific css has to come last to overwrite other styles
    'assets/client/note/client-note.css',
    'assets/client/read/client-read.css',
]);
$this->addAttribute('js', []);
// Js files that import things from other js files
$this->addAttribute('jsModules', ['assets/client/read/client-read-main.js']);

// Store client id on the page in <data> element for js to read it
?>
<data id="client-id" value="<?= html($clientReadData->id) ?>"></data>

<div id="title-and-dropdown-flexbox">
    <div id="outer-contenteditable-heading-container" data-deleted="<?= $clientReadData->deletedAt ? 1 : 0 ?>">
        <div class="inner-contenteditable-heading-div contenteditable-field-container" data-field-element="h1">
            <?php
            if (str_contains($clientReadData->generalPrivilege, 'U')) { ?>
                <!-- Img has to be before title because only the next sibling can be styled in css -->
                <img src="assets/general/general-img/action/material-edit-icon.svg"
                     class="contenteditable-edit-icon cursor-pointer"
                     alt="Edit"
                     id="edit-first-name-btn">
                <?php
            } ?>
            <h1 data-name="first_name" data-minlength="2" data-maxlength="100" spellcheck="false"><?=
                !empty($clientReadData->firstName) ? html($clientReadData->firstName) : '&nbsp;' ?></h1>
        </div>
        <div class="inner-contenteditable-heading-div contenteditable-field-container" data-field-element="h1">
            <?php
            if (str_contains($clientReadData->generalPrivilege, 'U')) { ?>
                <img src="assets/general/general-img/action/material-edit-icon.svg"
                     class="contenteditable-edit-icon cursor-pointer"
                     alt="Edit"
                     id="edit-last-name-btn">
                <?php
            } ?>
            <h1 data-name="last_name" data-minlength="2" data-maxlength="100" spellcheck="false"><?=
                !empty($clientReadData->lastName) ? html($clientReadData->lastName) : '&nbsp;' ?></h1>
        </div>
    </div>
    <!-- Status and assigned user select options containers -->
    <div id="status-and-assigned-user-select-container">
        <!-- Status select options-->
        <div>
            <label for="client-status" class="bigger-select-label"><?= __('Status') ?></label>
            <select name="client_status_id" class="default-select bigger-select"
                <?= str_contains($clientReadData->clientStatusPrivilege, 'U')
                    ? '' : 'disabled' ?>>
                <option value=""></option>
                <?php
                // Client status select options
                foreach ($dropdownValues->statuses as $statusId => $statusName) {
                    $selected = $statusId === $clientReadData->clientStatusId ? 'selected' : '';
                    echo '<option value="' . html($statusId) . '" ' . $selected . '>' . html($statusName) . '</option>';
                }
                ?>
            </select>
        </div>

        <!-- Assigned user select options-->
        <div>
            <label for="assigned-user-select" class="bigger-select-label"><?= __('Helper') ?></label>
            <select name="user_id" class="default-select bigger-select" id="assigned-user-select"
                <?= str_contains($clientReadData->assignedUserPrivilege, 'U') ? '' : 'disabled' ?>>
                <option value=""></option>
                <?php
                // Linked user select options
                foreach ($dropdownValues->users as $id => $name) {
                    $selected = $id === $clientReadData->userId ? 'selected' : '';
                    echo '<option value="' . html($id) . '" ' . $selected . '>' . html($name) . '</option>';
                }
                ?>
            </select>
        </div>
    </div>
</div>

<div id="main-note-div">
    <div id="main-note-textarea-div">
        <textarea name="message" class="auto-resize-textarea main-note-textarea"
                  minlength="0" maxlength="1000"
                  data-editable="<?= str_contains($clientReadData->mainNoteData->privilege, 'U') ? '1' : '0' ?>"
                  data-note-id="<?= html($clientReadData->mainNoteData->id ?? 'new-main - note') ?>"
                  placeholder="<?= __('No main note') ?>"
        ><?= html($clientReadData->mainNoteData->message) ?></textarea>
        <div class="circle-loader client-note">
            <div class="checkmark draw"></div>
        </div>
    </div>

</div>

<div id="client-activity-personal-info-container">
    <div id="client-note-wrapper" class="client-note-wrapper"
         data-notes-amount="<?= html($clientReadData->notesAmount) ?>">
        <div class="vertical-center" id="activity-header">
            <h2><?= __('Notes') ?></h2>
            <?php
            if (str_contains($clientReadData->noteCreationPrivilege, 'C')) { ?>
                <div class="plus-btn" id="create-note-btn"></div>
                <?php
            } ?>
        </div>
        <!--  Notes are populated here via ajax  -->
    </div>
    <div id="client-personal-info-container">
        <div id="client-personal-info-flex-container" style="<?= $clientReadData->birthdate || $clientReadData->sex ||
        $clientReadData->location || $clientReadData->phone || $clientReadData->email ? '' : 'opacity: 0;' ?>">
            <!-- Toggle edit icons on mobile if user has privilege to update something -->
            <?php
            if (str_contains($clientReadData->generalPrivilege, 'U')) { ?>
                <img src="assets/general/general-img/action/material-edit-icon.svg"
                     class="contenteditable-edit-icon cursor-pointer" alt="Edit"
                     id="toggle-personal-info-edit-icons">
                <?php
            } ?>
            <!-- id prefix has to be the same as alt attr of personal-info-icon inside here but also available icons -->
            <div id="birthdate-container" style="<?= $clientReadData->birthdate ? '' : 'display: none;' ?>">
                <!-- icon alt has to be the same as the input name -->
                <img src="assets/general/general-img/personal-data-icons/birthdate-icon.svg"
                     class="personal-info-icon default-icon" alt="birthdate">
                <div class="partial-personal-info-and-edit-icon-div contenteditable-field-container"
                     data-field-element="span"
                     data-hide-if-empty="true">
                    <?php
                    if (str_contains($clientReadData->generalPrivilege, 'U')) { ?>
                        <img src="assets/general/general-img/action/material-edit-icon.svg"
                             class="contenteditable-edit-icon cursor-pointer" alt="Edit"
                             id="edit-birthdate-btn">
                        <?php
                    } ?>
                    <span spellcheck="false" data-name="birthdate" data-maxlength="254"
                          class="contenteditable-placeholder" data-placeholder="dd.mm.yyyy"
                    ><?php
                        if ($clientReadData->birthdate) {
                            echo html($clientReadData->birthdate->format('d.m.Y')) ?><span id="age-sub-span"
                            >&nbsp; • &nbsp;<?=
                        html((new DateTime())->diff($clientReadData->birthdate)->y) ?></span><?php
                        } else {
                            echo '';
                        } ?></span>
                </div>
            </div>
            <div id="sex-container" style="<?= $clientReadData->sex ? '' : 'display: none;' ?>">
                <!-- icon alt has to be the same as the input name -->
                <img src="assets/general/general-img/personal-data-icons/gender-icon.svg"
                     class="personal-info-icon default-icon" alt="sex">
                <div class="partial-personal-info-and-edit-icon-div contenteditable-field-container"
                     data-field-element="select"
                     data-hide-if-empty="true">
                    <?php
                    if (str_contains($clientReadData->generalPrivilege, 'U')) { ?>
                        <img src="assets/general/general-img/action/material-edit-icon.svg"
                             class="contenteditable-edit-icon cursor-pointer" alt="Edit"
                             id="edit-sex-btn">
                        <select name="sex" class="default-select" id="sex-select">
                            <option value=""></option>
                            <?php
                            // Linked user select options
                            foreach ($dropdownValues->sexes as $id => $name) {
                                $selected = $id === $clientReadData->sex ? 'selected' : '';
                                echo '<option value="' . html($id) . '" ' . $selected . '>' . html($name) . '</option>';
                            }
                            ?>
                        </select>
                        <?php
                    } ?>

                    <span spellcheck="false" data-name="sex" data-maxlength="254"
                    ><?= $clientReadData->sex ? html($dropdownValues->sexes[$clientReadData->sex]) : '' ?></span>
                </div>
            </div>
            <a href="https://www.google.ch/maps/search/<?= html($clientReadData->location) ?>" target="_blank"
               id="location-container" style="<?= $clientReadData->location ? '' : 'display: none;' ?>">
                <!-- icon alt has to be the same as the input name -->
                <img src="assets/general/general-img/personal-data-icons/location-icon.svg"
                     class="personal-info-icon default-icon" alt="location">
                <div class="partial-personal-info-and-edit-icon-div contenteditable-field-container"
                     data-field-element="a-span"
                     data-hide-if-empty="true">
                    <?php
                    if (str_contains($clientReadData->generalPrivilege, 'U')) { ?>
                        <img src="assets/general/general-img/action/material-edit-icon.svg"
                             class="contenteditable-edit-icon cursor-pointer" alt="Edit"
                             id="edit-location-btn">
                        <?php
                    } ?>
                    <span data-name="location" data-minlength="2" data-maxlength="100" spellcheck="false"><?=
                        html($clientReadData->location) ?></span>
                </div>
            </a>
            <a href="tel:<?= html($clientReadData->phone) ?>" target="_blank" rel="noopener"
               id="phone-container" style="<?= $clientReadData->phone ? '' : 'display: none;' ?>">
                <!-- icon alt has to be the same as the input name -->
                <img src="assets/general/general-img/personal-data-icons/phone-icon.svg"
                     class="personal-info-icon default-icon" alt="phone">
                <div class="partial-personal-info-and-edit-icon-div contenteditable-field-container"
                     data-field-element="a-span"
                     data-hide-if-empty="true">
                    <?php
                    if (str_contains($clientReadData->generalPrivilege, 'U')) { ?>
                        <img src="assets/general/general-img/action/material-edit-icon.svg"
                             class="contenteditable-edit-icon cursor-pointer" alt="Edit"
                             id="edit-phone-btn">
                        <?php
                    } ?>
                    <span data-name="phone" data-minlength="3" data-maxlength="20" spellcheck="false"><?=
                        html($clientReadData->phone) ?></span>
                </div>
            </a>
            <a href="mailto:<?= html($clientReadData->email) ?>" target="_blank" rel="noopener"
               id="email-container" style="<?= $clientReadData->email ? '' : 'display: none;' ?>">
                <!-- icon alt has to be the same as the input name -->
                <img src="assets/general/general-img/personal-data-icons/email-icon.svg"
                     class="personal-info-icon default-icon" alt="email">
                <div id="email-div" class="partial-personal-info-and-edit-icon-div contenteditable-field-container"
                     data-field-element="a-span"
                     data-hide-if-empty="true">
                    <?php
                    if (str_contains($clientReadData->generalPrivilege, 'U')) { ?>
                        <img src="assets/general/general-img/action/material-edit-icon.svg"
                             class="contenteditable-edit-icon cursor-pointer" alt="Edit"
                             id="edit-email-btn">
                        <?php
                    } ?>
                    <?php
                    $emailParts = $clientReadData->email ? explode('@', $clientReadData->email) : null; ?>
                    <span id="email-prefix" spellcheck="false" data-name="email" data-maxlength="254"
                    ><?= $emailParts ? html($emailParts[0]) . ' <br>@' . html($emailParts[1]) : '' ?></span>
                </div>
            </a>
            <div id="vigilance_level-container" style="<?= $clientReadData->vigilanceLevel ? '' : 'display: none;' ?>">
                <!-- icon alt has to be the same as the input name -->
                <img src="assets/general/general-img/personal-data-icons/warning-icon.svg"
                     class="personal-info-icon default-icon" alt="vigilance_level">
                <div class="partial-personal-info-and-edit-icon-div contenteditable-field-container"
                     data-field-element="select"
                     data-hide-if-empty="true">
                    <?php
                    if (str_contains($clientReadData->generalPrivilege, 'U')) { ?>
                        <img src="assets/general/general-img/action/material-edit-icon.svg"
                             class="contenteditable-edit-icon cursor-pointer" alt="Edit"
                             id="edit-vigilance-level-btn">
                        <select name="vigilance_level" class="default-select" id="vigilance-level-select">
                            <option value=""><!-- is nullable --></option>
                            <?php
                            // Linked user select options
                            foreach ($dropdownValues->vigilanceLevel as $id => $name) {
                                $selected = $id === $clientReadData->vigilanceLevel?->value ? 'selected' : '';
                                echo '<option value="' . html($id) . '" ' . $selected . '>' . html($name) . '</option>';
                            }
                            ?>
                        </select>
                        <?php
                    } ?>
                    <span spellcheck="false" data-maxlength="254"
                    ><?= $clientReadData->vigilanceLevel ? html($clientReadData->vigilanceLevel->getDisplayName())
                            : '' ?></span>
                </div>
            </div>
        </div>
        <div class="clearfix"></div>
        <?php
        if (str_contains($clientReadData->generalPrivilege, 'U')) { ?>
            <div id="add-client-personal-info-div">
                <img src="assets/general/general-img/action/plus-icon.svg" id="toggle-personal-info-icons"
                     class="default-icon" alt="add info">

                <?php
                if (str_contains($clientReadData->generalPrivilege, 'D')) {
                    echo $clientReadData->deletedAt ? '<img src = "assets/general/general-img/action/undelete-icon.svg" 
                    class="default-icon personal-info-icon permanently-in-available-icon-div" id="undelete-client-btn" 
                    alt="undelete">' : '<img src = "assets/general/general-img/action/trash-icon.svg" 
                        class="personal-info-icon permanently-in-available-icon-div default-icon" 
                        id="delete-client-btn" alt="delete">';
                } ?>

                <!-- alt has to be exactly the same as the field name.
                The field container id has to be "[alt]-container".
                The edit icon image in the existing container has to have the same alt as the name as well. -->
                <img src="assets/general/general-img/personal-data-icons/birthdate-icon.svg"
                     class="personal-info-icon default-icon" alt="birthdate">
                <img src="assets/general/general-img/personal-data-icons/gender-icon.svg"
                     class="personal-info-icon default-icon" alt="sex">
                <img src="assets/general/general-img/personal-data-icons/location-icon.svg"
                     class="personal-info-icon default-icon" alt="location">
                <img src="assets/general/general-img/personal-data-icons/phone-icon.svg"
                     class="personal-info-icon default-icon" alt="phone">
                <img src="assets/general/general-img/personal-data-icons/email-icon.svg"
                     class="personal-info-icon default-icon" alt="email">
                <img src="assets/general/general-img/personal-data-icons/warning-icon.svg"
                     class="personal-info-icon default-icon" alt="vigilance_level">
            </div>
            <?php
        } ?>
    </div>
</div>