<?php
/**
 * @var \Slim\Views\PhpRenderer $this Rendering engine
 */

$this->setLayout('layout/layout.html.php');

// Define assets that should be included
// Populate variable $css for layout which then generates the HTML code to include assets
$this->addAttribute('css', [
    'assets/general/page-component/form/form.css',
    'assets/general/page-component/button/plus-button.css',
    'assets/general/page-component/modal/form-modal.css',
    'assets/general/page-component/skeleton-loader/skeleton-loader.css',
    'assets/user/list/user-list-skeleton-loader.css',
    // Page-specific css has to come last to overwrite other styles
    'assets/user/list/user-list.css',
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
    <h1><?= __('Users') ?></h1>
    <div class="plus-btn" id="create-user-btn"></div>
</div>

<div id="user-wrapper">
</div>