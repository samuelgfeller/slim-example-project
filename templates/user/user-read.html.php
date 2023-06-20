<?php
/**
 * @var \Slim\Interfaces\RouteParserInterface $route
 * @var $this \Slim\Views\PhpRenderer Rendering engine
 * @var $user \App\Domain\User\Data\UserResultData user
 * @var $userStatuses \App\Domain\User\Enum\UserStatus[] all user statuses
 * @var $userActivities \App\Domain\User\Data\UserActivityData[] all user activities
 * @var $isOwnProfile bool if authenticated user is viewing his own profile
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
    'assets/general/page-component/modal/form-modal.css',
    // profile.css has to come last to overwrite other styles
    'assets/general/dark-mode/dark-mode-switch.css',
    'assets/general/page-component/contenteditable/contenteditable.css',
    'assets/user/user.css',
]);


$this->addAttribute(
    'jsModules',
    ['assets/user/read/user-read-update-main.js', 'assets/general/dark-mode/dark-mode.js',]
);

// Store client id on the page in <data> element for js to read it
?>
<data id="user-id" value="<?= $user->id ?>"></data>
<data id="is-own-profile" value="<?= $isOwnProfile ? '1' : '0' ?>"></data>

<div id="user-page-content-flexbox">
    <div id="user-profile-content">
        <div id="full-header-edit-icon-container">
            <div class="partial-header-edit-icon-div contenteditable-field-container" data-field-element="h1">
                <?php
                if ($user->generalPrivilege->hasPrivilege(Privilege::UPDATE)) { ?>
                    <!-- Img has to be before title because we are only able to style next sibling in css -->
                    <img src="assets/general/general-img/material-edit-icon.svg"
                         class="contenteditable-edit-icon cursor-pointer"
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
                    <img src="assets/general/general-img/material-edit-icon.svg"
                         class="contenteditable-edit-icon cursor-pointer"
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
                <label for="user-status" class="bigger-select-label"><?= __('Status') ?></label>
                <select name="status" class="default-select bigger-select" id="user-status"
                    <?= $user->statusPrivilege->hasPrivilege(Privilege::UPDATE)
                        ? '' : 'disabled' ?>>
                    <?php
                    // User status select options
                    foreach ($userStatuses as $userStatus) {
                        $selected = $userStatus === $user->status ? 'selected' : '';
                        echo "<option value='$userStatus->value' $selected>" .
                            __(ucfirst($userStatus->value)) . "</option>";
                    }
?>
                </select>
            </div>

            <!-- Assigned user select options-->
            <div>
                <label for="user-role-select" class="bigger-select-label"><?= __('User role') ?> </label>
                <select name="user_role_id" class="default-select bigger-select" id="user-role-select"
                    <?= $user->userRolePrivilege->hasPrivilege(Privilege::UPDATE) ? '' : 'disabled' ?>>
                    <?php
foreach ($user->availableUserRoles as $id => $userRole) {
    $selected = $id === $user->userRoleId ? 'selected' : '';
    echo "<option value='$id' $selected>" . $userRole . "</option>";
}
?>
                </select>
            </div>
        </div>

        <h3 class="label-h3"><?= __('E-Mail') ?></h3>
        <div class="contenteditable-field-container user-field-value-container" data-field-element="span">
            <?php
            if ($user->generalPrivilege->hasPrivilege(Privilege::UPDATE)) { ?>
                <img src="assets/general/general-img/material-edit-icon.svg"
                     class="contenteditable-edit-icon cursor-pointer"
                     alt="Edit"
                     id="edit-email-btn">
                <?php
            } ?>
            <span spellcheck="false" data-name="email" data-maxlength="254"
            ><?= !empty($user->email) ? html($user->email) : '&nbsp;' ?></span>
        </div>

        <div>
            <?php
            if ($user->generalPrivilege->hasPrivilege(Privilege::UPDATE)) { ?>
                <h3 class="label-h3"><?= __('Password') ?></h3>
                <button class="btn btn-orange" id="change-password-btn"
                        data-old-password-requested="<?= $user->passwordWithoutVerificationPrivilege->hasPrivilege(
                            Privilege::UPDATE
                        ) ? 'false' : 'true' ?>"><?= __('Change password') ?>
                </button>
                <?php
            } ?>
        </div>
        <?php
        if ($isOwnProfile === true) { ?>
            <div>
                <h3 class="label-h3"><?= __('Dark mode') ?></h3>
                <label id="dark-mode-switch-container">
                    <input id='dark-mode-toggle-checkbox' type='checkbox'>
                    <div id='dark-mode-toggle-slot'>
                        <div id='dark-mode-sun-icon-wrapper'>
                            <!--<div class="iconify" id="dark-mode-sun-icon" data-icon="feather-sun" data-inline="false"></div>-->
                            <img src="assets/general/dark-mode/sun-icon.svg" alt="sun" id="dark-mode-sun-icon">
                        </div>
                        <div id="dark-mode-toggle-button"></div>
                        <div id='dark-mode-moon-icon-wrapper'>
                            <img src="assets/general/dark-mode/moon-icon.svg" alt="sun" id="dark-mode-moon-icon">
                        </div>
                    </div>
                </label>
            </div>
            <?php
        }
        $lang = $user->language?->value;
?>
        <div id="language-switch-div">
            <h3 class="label-h3"><?= __('Language') ?></h3>
            <label class="form-radio-input">
                <input type="radio" name="language" value="en_US" <?= $lang === 'en_US' ? 'checked' : '' ?>>
                English
            </label>
            <label class="form-radio-input">
                <input type="radio" name="language" value="de_CH" <?= $lang === 'de_CH' ? 'checked' : '' ?>>
                Deutsch
            </label>
            <label class="form-radio-input">
                <input type="radio" name="language" value="fr_CH" <?= $lang === 'fr_CH' ? 'checked' : '' ?>>
                Français
            </label>
        </div>
        <h3 class="label-h3"><?= __('Metadata') ?></h3>
        <p class="secondary-text"><b>ID:</b> <?= $user->id ?><br>
            <?php
            // Create date formatter that outputs the date in the correct lang in a format like February 12, 2023 at 8:45 PM
            $dateFormatter = new \IntlDateFormatter(
                setlocale(LC_ALL, 0),
                \IntlDateFormatter::LONG,
                \IntlDateFormatter::SHORT
            );
            ?>
            <b><?= __('Created') ?>:</b> <?= $dateFormatter->format($user->createdAt) ?><br>
            <b><?= __('Updated') ?>:</b> <?= $dateFormatter->format($user->updatedAt) ?>
        </p>
        <?php
        if ($user->generalPrivilege->hasPrivilege(Privilege::DELETE)) { ?>
            <button class="btn btn-red" id="delete-user-btn">
                <img class="icon-btn" src="assets/general/general-img/action/trash-icon.svg" alt="">
                <?= $isOwnProfile ? __('Delete profile') : __('Delete user') ?>
            </button>
            <?php
        } ?>
    </div>

    <div id="user-activity-container">
        <div id="user-activity-header">
            <h2><?= __('User activity') ?></h2>
        </div>
        <div id="user-activity-content">
        </div>
    </div>
</div>