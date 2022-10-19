<?php
/**
 * @var $this \Slim\Views\PhpRenderer Rendering engine
 * @var $userPosts \App\Domain\Post\Data\UserNoteData[]
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
    'assets/general/css/modal/form-modal.css',
    'assets/post/post.css',
]);
$this->addAttribute('js', ['assets/post/post.js', 'assets/general/js/modal.js']);
?>
<div class="vertical-middle">
    <h2>All posts</h2>
    <div class="plus-btn" id="create-post-btn"></div>
</div>
<!-- Post visibility scope is either "own" or "all" depending on the if current page shows only own posts or all posts.
All posts and own posts pages are quite similar and share the same create form and modal box. After the creation of
a post they are re-loaded in the background (async) to be up-to-date with the server -->



<div id="post-wrapper" data-post-visibility-scope="all">
    <!-- Flexbox -->
</div>
