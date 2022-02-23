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
    'assets/post/post.css',
]);
$this->addAttribute('js', ['assets/post/post.js', 'assets/general/js/modal.js']);
?>
<div class="vertical-middle">
    <h2>All posts</h2>
    <div class="plusBtn" id="create-post-btn"></div>
</div>
<!-- Post visibility scope is either "own" or "all" depending on the if current page shows only own posts or all posts.
All posts and own posts pages are quite similar and share the same create form and modal box. After the creation of
a post they are re-loaded in the background (async) to be up-to-date with the server -->
<div id="post-wrapper" data-post-visibility-scope="all">
    <?php
    foreach ($userPosts as $userPost) { ?>
        <div class="post-squares" id="post<?= $userPost->postId ?>">
            <div class="box-content">
                <div class="loader" id="loaderForPost<?= $userPost->postId ?>"></div>
                <h3 class="box-header"><?= $userPost->userName ?></h3>
                <div id="box-inner-content<?= $userPost->postId ?>">
                    <p><span class="info-in-box-span"></span><b><?= $userPost->postMessage ?></b></p>
                    <p><span class="info-in-box-span">Updated at: </span><b><?= $userPost->postUpdatedAt ?></b></p>
                    <p><span class="info-in-box-span">Created at: </span><?= $userPost->postCreatedAt ?></p>
                </div>
            </div>
        </div>
        <?php
    } ?>
</div>

    <div id="create-post-div">
    </div>
    <!--<div id="postsDiv">-->
    <!--    <p>Loading...</p>-->
    <!--</div>-->
    <div class="clearfix"></div>
