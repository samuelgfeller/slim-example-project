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
    'assets/post/post.css'
]);
$this->addAttribute('js', ['assets/user/profile.js']);
?>
<div class="verticalCenter">
    <h2 style="display:inline-block;">All posts</h2>
    <div class="plusBtn" id="createPostBtn"></div>
</div>
<div id="post-wrapper">
    <?php
    foreach ($userPosts as $userPost) { ?>
        <div class="single-box" id="post<?= $userPost->postId ?>">
            <div class="box-content">
                <img src="/img/edit_icon.svg" class="post-edit-icon cursorPointer" data-id="<?= $userPost->postId ?>" alt="edit">
                <img src="/img/del_icon.svg" class="del-icon cursorPointer" data-id="<?= $userPost->postId ?>" alt="del">
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
