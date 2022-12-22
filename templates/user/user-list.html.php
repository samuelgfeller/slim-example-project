<?php
/**
 * @var $this \Slim\Views\PhpRenderer Rendering engine
 */

$this->setLayout('layout.html.php');

// Define assets that should be included
// Populate variable $css for layout which then generates the HTML code to include assets
$this->addAttribute('css', [
    'assets/general/page-component/form/form.css',
    'assets/general/page-component/button/plus-button.css',
    'assets/general/page-component/modal/form-modal.css',
    'assets/general/page-component/content-placeholder/content-placeholder.css',
    'assets/user/list/user-list-content-placeholder.css',
    'assets/user/list/user-list.css',
    // post.css has to come last to overwrite other styles
]);
// Js files that import things from other js files
$this->addAttribute(
    'jsModules',
    [
        'assets/user/list/user-list-main.js',
        'assets/user/create/user-create-main.js',
    ]
);

?>
<div class="vertical-center">
    <h1>Users</h1>
    <div class="plus-btn" id="create-user-btn"></div>
</div>

<div id="user-wrapper">
</div>