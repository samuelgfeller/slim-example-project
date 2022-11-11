<?php
/**
 * @var \Slim\Interfaces\RouteParserInterface $route
 * @var $this \Slim\Views\PhpRenderer Rendering engine
 * @var $user \App\Domain\User\Data\UserResultData logged in user
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
    'assets/general/css/loader/three-dots-loader.css',
    // profile.css has to come last to overwrite other styles
    'assets/general/css/contenteditable.css',
]);

$this->addAttribute('jsModules', ['assets/user/read/user-read-main.js',]);

// Store client id on the page in <data> element for js to read it
?>
<data id="user-id" value="<?= $user->id ?>"></data>

<div id="full-header-edit-icon-container">
        <div class="partial-header-edit-icon-div" data-field-element="h1">
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
        <div class="partial-header-edit-icon-div" data-field-element="h1">
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
<!-- CSS Grid -->
<div id="personal-info-wrapper"
     data-id="<?= $user->id /* Put user id into wrapper as it's the same for all values to change in this page */ ?>">
    <div>
        <label class="profile-value-title" for="first-name-input">Firstname:</label>
        <!--        <button class="btn edit-profile-value-btn">Edit</button>-->
        <div class="profile-value-div">
            <!-- This span has to be before the edit icon as it's used in js with previousElementSibling -->
            <span class="profile-value"><?= $user->firstName ?></span>
            <img src="assets/general/img/edit-icon.svg" class="contenteditable-edit-icon cursor-pointer" alt="Edit"
                 id="edit-first-name-ico">
        </div>
    </div>
    <div>
        <label class="profile-value-title" for="surname-input">Surname:</label>
        <div class="profile-value-div">
            <span id="surname-val" class="profile-value"><?= $user->surname ?></span>
            <img src="assets/general/img/edit-icon.svg" class="contenteditable-edit-icon cursor-pointer" alt="Edit"
                 id="edit-surname-ico">
        </div>
    </div>
    <div>
        <label class="profile-value-title" for="email-input">Email:</label>
        <div class="profile-value-div">
            <span id="email-val" class="profile-value"><?= $user->email ?></span>
            <img src="assets/general/img/edit-icon.svg" class="contenteditable-edit-icon cursor-pointer" alt="Edit"
                 id="edit-email-ico">
        </div>
    </div>
    <div>
        <a class="btn" id="change-password-btn" href="<?= $route->urlFor('change-password-page')?>">Change password</a>
    </div>
    <div>
        <button type="button" class="btn btn-red" id="delete-account-btn">Delete account</button>
    </div>
</div>

<br>

<p class="secondary-text"><i><b>Id:</b> <?= $user->id ?></i><br>
<i><b>Status:</b> <?= $user->status?->value ?></i><br>
<i><b>Role:</b> <?= $user->role ?></i><br>
<i><b>Created:</b> <?= date('d.m.Y H:i:s ', strtotime($user->createdAt)) ?></i><br>
<i><b>Updated:</b> <?= date('d.m.Y H:i:s', strtotime($user->updatedAt)) ?></i></p>