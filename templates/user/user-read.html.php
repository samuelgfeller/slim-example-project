<?php
/**
 * @var \Slim\Interfaces\RouteParserInterface $route
 * @var $this \Slim\Views\PhpRenderer Rendering engine
 * @var $user \App\Domain\User\Data\UserResultData user
 * @var $userStatuses \App\Domain\User\Enum\UserStatus[] all user statuses
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
    'assets/general/css/modal/form-modal.css',
    // profile.css has to come last to overwrite other styles
    'assets/general/css/contenteditable.css',
    'assets/user/user.css',
]);

$this->addAttribute('jsModules', ['assets/user/read/user-read-update-main.js',]);

// Store client id on the page in <data> element for js to read it
?>
<data id="user-id" value="<?= $user->id ?>"></data>

<div id="full-header-edit-icon-container">
    <div class="partial-header-edit-icon-div contenteditable-field-container" data-field-element="h1">
        <?php
        if ($user->generalPrivilege->hasPrivilege(Privilege::UPDATE)) { ?>
            <!-- Img has to be before title because we are only able to style next sibling in css -->
            <img src="assets/general/img/material-edit-icon.svg" class="contenteditable-edit-icon cursor-pointer"
                 alt="Edit"
                 id="edit-first-name-btn">
            <?php
        } ?>
        <h1 data-name="first_name" data-minlength="2" data-maxlength="100" spellcheck="false"><?=
            !empty($user->firstName) ? html($user->firstName) : '&nbsp;' ?></h1>
    </div>
    <div class="partial-header-edit-icon-div contenteditable-field-container" data-field-element="h1">
        <?php
        if ($user->generalPrivilege->hasPrivilege(Privilege::UPDATE)) { ?>
            <img src="assets/general/img/material-edit-icon.svg" class="contenteditable-edit-icon cursor-pointer"
                 alt="Edit"
                 id="edit-last-name-btn">
            <?php
        } ?>
        <h1 data-name="surname" data-minlength="2" data-maxlength="100" spellcheck="false"><?=
            !empty($user->surname) ? html($user->surname) : '&nbsp;' ?></h1>
    </div>
</div>


<div id="user-dropdown-container">
    <!-- Status select options-->
    <div>
        <label for="user-status" class="bigger-select-label">Status</label>
        <select name="status" class="default-select bigger-select" id="user-status"
            <?= $user->statusPrivilege->hasPrivilege(Privilege::UPDATE)
                ? '' : 'disabled' ?>>
            <?php
            // User status select options
            foreach ($userStatuses as $userStatus) {
                $selected = $userStatus === $user->status ? 'selected' : '';
                echo "<option value='$userStatus->value' $selected>" .
                    ucfirst($userStatus->value) . "</option>";
            }
            ?>
        </select>
    </div>

    <!-- Assigned user select options-->
    <div>
        <label for="user-role-select" class="bigger-select-label">User role</label>
        <select name="user_role_id" class="default-select bigger-select" id="user-role-select"
            <?= $user->userRolePrivilege->hasPrivilege(Privilege::UPDATE) ? '' : 'disabled' ?>>
            <?php
            foreach ($user->availableUserRoles as $id => $userRole) {
                $selected = $id === $user->user_role_id ? 'selected' : '';
                echo "<option value='$id' $selected>" .
                    ucfirst(str_replace('_', ' ', $userRole)) . "</option>";
            }
            ?>
        </select>
    </div>
</div>

<h3 class="label-h3">E-Mail</h3>
<div class="contenteditable-field-container user-field-value-container" data-field-element="span">
    <?php
    if ($user->generalPrivilege->hasPrivilege(Privilege::UPDATE)) { ?>
        <img src="assets/general/img/material-edit-icon.svg" class="contenteditable-edit-icon cursor-pointer"
             alt="Edit"
             id="edit-email-btn">
        <?php
    } ?>
    <span spellcheck="false" data-name="email" data-maxlength="254"
    ><?= !empty($user->email) ? html($user->email) : '&nbsp;' ?></span>
</div>

<div>
<h3 class="label-h3">Password</h3>
    <button class="btn" id="change-password-btn">Change password</button>
</div>
<!--<div>-->
<!--    <button type="button" class="btn btn-red" id="delete-account-btn">Delete account</button>-->
<!--</div>-->

<h3 class="label-h3">Metadata</h3>
<p class="secondary-text"><b>ID:</b> <?= $user->id ?><br>
    <b>Created:</b> <?= date('d. F Y • H:i:s ', strtotime($user->createdAt)) ?><br>
    <b>Updated:</b> <?= date('d. F Y • H:i:s', strtotime($user->updatedAt)) ?></p>