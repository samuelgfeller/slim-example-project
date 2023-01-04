<?php
/**
 * @var \Slim\Interfaces\RouteParserInterface $route
 * @var $this \Slim\Views\PhpRenderer Rendering engine
 * @var $clientAggregate \App\Domain\Client\Data\ClientResultData client
 * @var $dropdownValues App\Domain\Client\Data\ClientDropdownValuesData all statuses, users and sexes to populate dropdown
 */

use App\Domain\Authorization\Privilege;

$this->setLayout('layout.html.php');
?>

<?php
// Define assets that should be included
// Populate variable $css for layout which then generates the HTML code to include assets
$this->addAttribute('css', [
    'assets/general/page-component/form/form.css',
    'assets/general/page-component/modal/alert-modal.css',
    'assets/general/page-component/loader/animated-checkmark.css',
    'assets/general/page-component/button/plus-button.css',
    'assets/general/page-component/content-placeholder/content-placeholder.css',
    'assets/general/page-component/contenteditable/contenteditable.css',
    // page specific css has to come last to overwrite other styles
    'assets/client/note/client-note.css',
    'assets/client/read/client-read.css',
]);
$this->addAttribute('js', []);
// Js files that import things from other js files
$this->addAttribute('jsModules', ['assets/client/read/client-read-main.js']);

// Store client id on the page in <data> element for js to read it
?>
<data id="client-id" value="<?= $clientAggregate->id ?>"></data>

<div id="title-and-dropdown-flexbox">
    <div id="full-header-edit-icon-container" data-deleted="<?= $clientAggregate->deletedAt ? '1' : 0 ?>">
        <div class="partial-header-edit-icon-div contenteditable-field-container" data-field-element="h1">
            <?php
            if ($clientAggregate->mainDataPrivilege->hasPrivilege(Privilege::UPDATE)) { ?>
                <!-- Img has to be before title because we are only able to style next sibling in css -->
                <img src="assets/general/general-img/material-edit-icon.svg"
                     class="contenteditable-edit-icon cursor-pointer"
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
                <img src="assets/general/general-img/material-edit-icon.svg"
                     class="contenteditable-edit-icon cursor-pointer"
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
                <option value=""></option>
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
                <option value=""></option>
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
                  minlength="0" maxlength="1000"
                  data-editable="<?= $clientAggregate->mainNoteData
                      ->privilege->hasPrivilege(Privilege::UPDATE) ? '1' : '0' ?>"
                  data-note-id="<?= $clientAggregate->mainNoteData->id ?? 'new-main-note' ?>"
                  placeholder="New main note"
        ><?= html($clientAggregate->mainNoteData->message) ?></textarea>
        <div class="circle-loader client-note">
            <div class="checkmark draw"></div>
        </div>
    </div>

</div>

<div id="client-activity-personal-info-container">
    <div id="client-note-wrapper" class="client-note-wrapper" data-notes-amount="<?= $clientAggregate->notesAmount ?>">
        <div class="vertical-center" id="activity-header">
            <h2>Aktivität</h2>
            <?php
            if ($clientAggregate->noteCreatePrivilege->hasPrivilege(Privilege::ONLY_CREATE)) { ?>
                <div class="plus-btn" id="create-note-btn"></div>
                <?php
            } ?>
        </div>
        <!--  Notes are populated here via ajax  -->
    </div>
    <div id="client-personal-info-container">
        <div id="client-personal-info-flex-container" style="<?= $clientAggregate->birthdate || $clientAggregate->sex ||
        $clientAggregate->location || $clientAggregate->phone || $clientAggregate->email ? '' : 'opacity: 0;' ?>">
            <!-- Toggle edit icons on mobile -->
            <img src="assets/general/general-img/material-edit-icon.svg"
                 class="contenteditable-edit-icon cursor-pointer" alt="Edit"
                 id="toggle-personal-info-edit-icons">
            <!-- id prefix has to be the same as alt attr of personal-info-icon inside here but also available icons -->
            <div id="birthdate-container" style="<?= $clientAggregate->birthdate ? '' : 'display: none;' ?>">
                <!-- icon alt has to be the same as the input name -->
                <img src="assets/general/general-img/birthdate-icon.svg" class="personal-info-icon" alt="birthdate">
                <div class="partial-personal-info-and-edit-icon-div contenteditable-field-container"
                     data-field-element="span"
                     data-hide-if-empty="true">
                    <?php
                    if ($clientAggregate->mainDataPrivilege->hasPrivilege(Privilege::UPDATE)) { ?>
                        <img src="assets/general/general-img/material-edit-icon.svg"
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
                <!-- icon alt has to be the same as the input name -->
                <img src="assets/general/general-img/personal-data-icons/gender-icon.svg" class="personal-info-icon"
                     alt="sex">
                <div class="partial-personal-info-and-edit-icon-div contenteditable-field-container"
                     data-field-element="select"
                     data-hide-if-empty="true">
                    <?php
                    if ($clientAggregate->mainDataPrivilege->hasPrivilege(Privilege::UPDATE)) { ?>
                        <img src="assets/general/general-img/material-edit-icon.svg"
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
                <!-- icon alt has to be the same as the input name -->
                <img src="assets/general/general-img/personal-data-icons/location-icon.svg" class="personal-info-icon"
                     alt="location">
                <div class="partial-personal-info-and-edit-icon-div contenteditable-field-container"
                     data-field-element="a-span"
                     data-hide-if-empty="true">
                    <?php
                    if ($clientAggregate->mainDataPrivilege->hasPrivilege(Privilege::UPDATE)) { ?>
                        <img src="assets/general/general-img/material-edit-icon.svg"
                             class="contenteditable-edit-icon cursor-pointer" alt="Edit"
                             id="edit-location-btn">
                        <?php
                    } ?>
                    <span data-name="location" data-minlength="2" data-maxlength="100" spellcheck="false"><?=
                        html($clientAggregate->location) ?></span>
                </div>
            </a>
            <a href="tel:<?= $clientAggregate->phone ?>" target="_blank" rel="noopener"
               id="phone-container" style="<?= $clientAggregate->phone ? '' : 'display: none;' ?>">
                <!-- icon alt has to be the same as the input name -->
                <img src="assets/general/general-img/personal-data-icons/phone-icon.svg" class="personal-info-icon"
                     alt="phone">
                <div class="partial-personal-info-and-edit-icon-div contenteditable-field-container"
                     data-field-element="a-span"
                     data-hide-if-empty="true">
                    <?php
                    if ($clientAggregate->mainDataPrivilege->hasPrivilege(Privilege::UPDATE)) { ?>
                        <img src="assets/general/general-img/material-edit-icon.svg"
                             class="contenteditable-edit-icon cursor-pointer" alt="Edit"
                             id="edit-phone-btn">
                        <?php
                    } ?>
                    <span data-name="phone" data-minlength="3" data-maxlength="20" spellcheck="false"><?=
                        html($clientAggregate->phone) ?></span>
                </div>
            </a>
            <a href="mailto:<?= $clientAggregate->email ?>" target="_blank" rel="noopener"
               id="email-container" style="<?= $clientAggregate->email ? '' : 'display: none;' ?>">
                <!-- icon alt has to be the same as the input name -->
                <img src="assets/general/general-img/personal-data-icons/email-icon.svg" class="personal-info-icon"
                     alt="email">
                <div id="email-div" class="partial-personal-info-and-edit-icon-div contenteditable-field-container"
                     data-field-element="a-span"
                     data-hide-if-empty="true">
                    <?php
                    if ($clientAggregate->mainDataPrivilege->hasPrivilege(Privilege::UPDATE)) { ?>
                        <img src="assets/general/general-img/material-edit-icon.svg"
                             class="contenteditable-edit-icon cursor-pointer" alt="Edit"
                             id="edit-email-btn">
                        <?php
                    } ?>
                    <?php
                    $emailParts = $clientAggregate->email ? explode('@', $clientAggregate->email) : null; ?>
                    <span id="email-prefix" spellcheck="false" data-name="email" data-maxlength="254"
                    ><?= $emailParts ? html($emailParts[0]) . '<br>@' . html($emailParts[1]) : '' ?></span>
                </div>
            </a>
            <div id="vigilance_level-container" style="<?= $clientAggregate->vigilanceLevel ? '' : 'display: none;' ?>">
                <!-- icon alt has to be the same as the input name -->
                <img src="assets/general/general-img/personal-data-icons/warning-icon.svg" class="personal-info-icon"
                     alt="vigilance_level">
                <div class="partial-personal-info-and-edit-icon-div contenteditable-field-container"
                     data-field-element="select"
                     data-hide-if-empty="true">
                    <?php
                    if ($clientAggregate->mainDataPrivilege->hasPrivilege(Privilege::UPDATE)) { ?>
                        <img src="assets/general/general-img/material-edit-icon.svg"
                             class="contenteditable-edit-icon cursor-pointer" alt="Edit"
                             id="edit-vigilance-level-btn">
                        <select name="vigilance_level" class="default-select" id="vigilance-level-select">
                            <option value=""><!-- is nullable --></option>
                            <?php
                            // Linked user select options
                            foreach ($dropdownValues->vigilanceLevel as $id => $name) {
                                $selected = $id === $clientAggregate->vigilanceLevel?->value ? 'selected' : '';
                                echo "<option value='$id' $selected>$name</option>";
                            }
                            ?>
                        </select>
                        <?php
                    } ?>
                    <span spellcheck="false" data-maxlength="254"
                    ><?= $clientAggregate->vigilanceLevel ? $clientAggregate->vigilanceLevel->prettyName()
                            : '' ?></span>
                </div>
            </div>
        </div>
        <div class="clearfix"></div>
        <?php
        if ($clientAggregate->mainDataPrivilege->hasPrivilege(Privilege::UPDATE)) { ?>
            <div id="add-client-personal-info-div">
                <img src="assets/general/general-img/plus-icon.svg" id="toggle-personal-info-icons" alt="add info">

                <!-- Delete trash icon stays always there -->
                <?= $clientAggregate->mainDataPrivilege->hasPrivilege(Privilege::DELETE) ?
                    ($clientAggregate->deletedAt ? '<img src="assets/general/general-img/action/undelete-icon.svg" 
                    class="personal-info-icon permanently-in-available-icon-div" id="undelete-client-btn" alt="undelete">' :
                        '<img src="assets/general/general-img/action/trash-icon.svg" class="personal-info-icon permanently-in-available-icon-div" 
                        id="delete-client-btn" alt="delete">') : '' ?>

                <!-- alt has to be exactly the same as the field name.
                The field container id has to be "[alt]-container".
                The edit icon image in the existing container has to have the same alt as the name as well. -->
                <img src="assets/general/general-img/birthdate-icon.svg" class="personal-info-icon" alt="birthdate">
                <img src="assets/general/general-img/personal-data-icons/gender-icon.svg" class="personal-info-icon"
                     alt="sex">
                <img src="assets/general/general-img/personal-data-icons/location-icon.svg" class="personal-info-icon"
                     alt="location">
                <img src="assets/general/general-img/personal-data-icons/phone-icon.svg" class="personal-info-icon"
                     alt="phone">
                <img src="assets/general/general-img/personal-data-icons/email-icon.svg" class="personal-info-icon"
                     alt="email">
                <img src="assets/general/general-img/personal-data-icons/warning-icon.svg" class="personal-info-icon"
                     alt="vigilance_level">
            </div>
            <?php
        } ?>
    </div>
</div>