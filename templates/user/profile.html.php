<?php
/**
 * @var $this \Slim\Views\PhpRenderer Rendering engine
 * @var $user \App\Domain\User\Data\UserData logged in user
 */

$this->setLayout('layout.html.php');
?>

<!-- Define assets that should be included -->
<?php
// Populate variable $css for layout which then generates the HTML code to include assets
$this->addAttribute('css', ['assets/general/css/form.css', 'assets/user/profile.css']); // profile.css has to come last
$this->addAttribute('js', ['assets/user/profile.js']);
?>

<!--https://samuel-gfeller.ch/favicon.ico-->
<!--<img src="/assets/hello/slim-icon.png" alt="favicon">-->
<pre></pre>

<h1>Your profile</h1>

<!--<div class="test-grid">-->
<!--    <div class="profile-value-title">Firstname</div>-->
<!--    <div>-->
<!--    <div>--> <?php
//= $user->firstName ?>   <!--</div>-->
<!--    <div><button class="btn edit-profile-value-btn">Edit</button></div></div>-->
<!--    <div>asdf</div>-->
<!--    <div>asdf</div>-->
<!--</div>-->

<div id="personal-info-wrapper">
    <div>
        <label class="profile-value-title" for="first-name-input">Firstname:</label>
        <!--        <button class="btn edit-profile-value-btn">Edit</button>-->
        <div class="profile-value-div">
            <span id="first-name-val" class="profile-value"><?= $user->firstName ?></span>
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
            <img src="assets/general/img/edit_icon.svg" class="edit-icon cursor-pointer" alt="Edit" id="edit-email-ico">
        </div>
    </div>
</div>

<!--<table id="personal-info-table">-->
<!--    <tr>-->
<!--        <th class="profile-value-title">Firstname:</th>-->
<!--        <td id="first-name-val" class="profile-value">--><?php
// $user->firstName ?> <!--</td>-->
<!--        <td><button class="btn edit-profile-value-btn">Edit</button></td>-->
<!--    </tr>-->
<!--    <tr>-->
<!--        <th class="profile-value-title">Surname:</th>-->
<!--        <td id="surname-val" class="profile-value">--><?php
// $user->surname ?> <!--</td>-->
<!--        <td><button class="btn edit-profile-value-btn">Edit</button></td>-->
<!--    </tr>-->
<!--    <tr>-->
<!--        <th class="profile-value-title">Email:</th>-->
<!--        <td id="email-val" class="profile-value">--><?php
// $user->email ?> <!--</td>-->
<!--        <td><button class="btn edit-profile-value-btn">Edit</button></td>-->
<!--    </tr>-->
<!--</table>-->


<br><br><br>
<p><i>Id: <?= $user->id ?></i></p>
<p><i>Status: <?= $user->status ?></i></p>
<p><i>Role: <?= $user->role ?></i></p>
<p><i>Created: <?= date('d.m.Y H:i:s ', strtotime($user->createdAt)) ?></i></p>
<p><i>Updated: <?= date('d.m.Y H:i:s', strtotime($user->updatedAt)) ?></i></p>