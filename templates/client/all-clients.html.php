<?php
/**
 * @var $this \Slim\Views\PhpRenderer Rendering engine
 * @var $userPosts \App\Domain\Post\Data\UserPostData[]
 */

$this->setLayout('layout.html.php');
?>

<?php
// Define assets that should be included
// Populate variable $css for layout which then generates the HTML code to include assets
$this->addAttribute('css', [
//    'assets/general/css/loader/three-dots-loader.css',
    // post.css has to come last to overwrite other styles
    'assets/general/css/form.css',
    'assets/general/css/plus-button.css',
    'assets/general/css/modal.css',
    'assets/client/client.css',
]);
$this->addAttribute('js', ['assets/client/client.js', 'assets/general/js/modal.js']);
?>
<h1>Clients</h1>
<!-- Post visibility scope is either "own" or "all" depending on the if current page shows only own posts or all posts.
All posts and own posts pages are quite similar and share the same create form and modal box. After the creation of
a post they are re-loaded in the background (async) to be up-to-date with the server -->

<div id="client-wrapper" data-client-filter="all">
    <!-- Flexbox -->
    <div class="client-profile-card">
        <div class="profile-card-header">
            <!-- other div needed to attach bubble to img -->
            <div class="profile-card-avatar">
                <img src="assets/client/img/avatar_female.svg" alt="avatar">
                <span class="profile-card-age">41</span>
            </div>
        </div>
        <div class="profile-card-content">
            <h3>Rachel Harmon</h3>

            <div class="profile-card-infos-flexbox">
                <div>
                    <img src="assets/client/img/location_pin_icon.svg" class="profile-card-content-icon" alt="location">
                    <span>Basel</span>
                </div>
                <div>
                    <img src="assets/client/img/phone.svg" class="profile-card-content-icon" alt="phone">
                    <span>079 572 99 32</span>
                </div>
            </div>
            <div class="profile-card-assignee-and-status">
                <div>
                <select name="assigned-user">
                    <option value="1">Samuel</option>
                    <option value="2">Peter</option>
                </select>
                </div>
                <div>
                <select name="status">
                    <option value="1">Needs attention</option>
                    <option value="2">Done</option>
                </select>
                </div>
            </div>

        </div>
    </div>

    <div class="client-profile-card">
        <div class="profile-card-header">
            <!-- other div needed to attach bubble to img -->
            <div class="profile-card-avatar">
                <img src="assets/client/img/avatar_male.svg" alt="avatar">
                <span class="profile-card-age">28</span>
            </div>
        </div>
        <div class="profile-card-content">
            <h3>Timon Koch</h3>

            <div class="profile-card-infos-flexbox">
                <div>
                    <img src="assets/client/img/location_pin_icon.svg" class="profile-card-content-icon" alt="location">
                    <span>Bern</span>
                </div>
                <div>
                    <img src="assets/client/img/phone.svg" class="profile-card-content-icon" alt="phone">
                    <span>076 823 82 33</span>
                </div>
            </div>
            <div class="profile-card-assignee-and-status">
                <div>
                    <select name="assigned-user">
                        <option value="1">Samuel</option>
                        <option value="2">Peter</option>
                    </select>
                </div>
                <div>
                    <select name="status">
                        <option value="1">Needs attention</option>
                        <option value="2">Done</option>
                    </select>
                </div>
            </div>

        </div>
    </div>

</div>
