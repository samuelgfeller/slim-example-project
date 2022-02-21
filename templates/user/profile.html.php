<?php
/**
 * @var $this \Slim\Views\PhpRenderer Rendering engine
 * @var $user \App\Domain\User\Data\UserData logged in user
 */

$this->setLayout('layout.html.php');
?>

<?php
// Define assets that should be included
// Populate variable $css for layout which then generates the HTML code to include assets
$this->addAttribute('css', [
    'assets/general/css/form.css',
    'assets/general/css/loader/three-dots-loader.css',
    // profile.css has to come last to overwrite other styles
    'assets/user/profile.css'
]);
$this->addAttribute('js', ['assets/user/profile.js']);
?>

<h1>Your profile</h1>
<div id="personal-info-wrapper"
     data-id="<?= $user->id /* Put user id into wrapper as it's the same for all values to change in this page */ ?>">
    <div>
        <label class="profile-value-title" for="first-name-input">Firstname:</label>
        <!--        <button class="btn edit-profile-value-btn">Edit</button>-->
        <div class="profile-value-div">
            <!-- This span has to be before the edit icon as it's used in js with previousElementSibling -->
            <span class="profile-value"><?= $user->firstName ?></span>
            <img src="assets/general/img/edit_icon.svg" class="edit-icon cursor-pointer" alt="Edit"
                 id="edit-first-name-ico">
        </div>
    </div>
    <div>
        <label class="profile-value-title" for="surname-input">Surname:</label>
        <div class="profile-value-div">
            <span id="surname-val" class="profile-value"><?= $user->surname ?></span>
            <img src="assets/general/img/edit_icon.svg" class="edit-icon cursor-pointer" alt="Edit"
                 id="edit-surname-ico">
        </div>
    </div>
    <div>
        <label class="profile-value-title" for="email-input">Email:</label>
        <div class="profile-value-div">
            <span id="email-val" class="profile-value"><?= $user->email ?></span>
            <img src="assets/general/img/edit_icon.svg" class="edit-icon cursor-pointer" alt="Edit"
                 id="edit-email-ico">
        </div>
    </div>
</div>


<br><br><br>
<p><i><b>Id:</b> <?= $user->id ?></i></p>
<p><i><b>Status:</b> <?= $user->status ?></i></p>
<p><i><b>Role:</b> <?= $user->role ?></i></p>
<p><i><b>Created:</b> <?= date('d.m.Y H:i:s ', strtotime($user->createdAt)) ?></i></p>
<p><i><b>Updated:</b> <?= date('d.m.Y H:i:s', strtotime($user->updatedAt)) ?></i></p>