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
        <textarea name="message" class="auto-resize-textarea main-textarea"
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

    <div id="client-activity-textarea-container">
        <div class="vertical-center" id="activity-header">
            <h2>Aktivität</h2>
            <div class="plus-btn" id="create-note-btn"></div>
        </div>
        <?php
        //! ANY NOTE HTML THAT IS CHANGED BELOW HAS TO ADAPTED IN client-read-create-note.js AS WELL addNewNoteTextarea, populateNewNoteDomAttributes
        foreach ($clientAggregate->notes as $note) {
            // Textarea and loader have to be in a div for the absolute positioned loaders to know to which textarea they belong
            // If below is changed, addNewNoteTextarea() and insertNewNoteToDb() callback have to be updated as well
            ?>
            <div id="note<?= $note->noteId ?>-container" class="note-container">
                <label for="note<?= $note->noteId ?>"
                       class="discrete-label textarea-label"><span class="label-user-full-name"><?= html(
                            $note->userFullName
                        ) ?>
                    </span>
                    <?php
                    if ($note->userMutationRight === 'all') { ?>
                        <img class="delete-note-btn" alt="delete" src="assets/general/img/del-icon.svg"
                             data-note-id="<?= $note->noteId ?>"><span
                                class="discrete-text note-created-date"><?=
                            (new \DateTime($note->noteCreatedAt))->format('d.m.Y • H:i') ?></span>
                        <?php
                    } ?></label>
                <!-- Extra div necessary to position circle loader to relative parent without taking label into account -->
                <div class="relative">
                    <!-- Textarea opening and closing has to be on the same line to prevent unnecessary line break -->
                    <textarea class="auto-resize-textarea" id="note<?= $note->noteId ?>"
                              data-note-id="<?= $note->noteId ?>"
                              minlength="4" maxlength="500"
                              data-editable="<?= $note->userMutationRight === 'all' ? 1 : 0 ?>"
                              name="message"><?= html($note->noteMessage) ?></textarea>
                    <div class="circle-loader client-read" data-note-id="<?= $note->noteId ?>">
                        <div class="checkmark draw"></div>
                    </div>
                </div>
            </div>

            <?php
        } ?>
    </div>

    <div id="client-personal-info-flex-container">

        <?php
        if ($clientAggregate->phone) { ?>
            <div tabindex="0">
                <img src="assets/client/img/location_pin_icon.svg" class="default-icon" alt="location">
                <span><?= $clientAggregate->location ?></span>
            </div>
            <?php
        }
        if ($clientAggregate->phone) { ?>
            <div tabindex="0">
                <img src="assets/client/img/phone.svg" class="profile-card-content-icon" alt="phone">
                <span><?= $clientAggregate->phone ?></span>
            </div>
            <?php
        }
        if ($clientAggregate->email) { ?>
            <div tabindex="0">
                <img src="assets/client/img/email-icon.svg" class="profile-card-content-icon" alt="phone">
                <span><?= $clientAggregate->email ?></span>
            </div>
            <?php
        } ?>
    </div>

</div>