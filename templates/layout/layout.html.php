<!DOCTYPE html>
<html lang="en">
<head>
    <!--  Trailing slash has to be avoided on asset paths. Otherwise <base> does not work  -->
    <base href="<?= $basePath ?>/"/>
    <meta http-equiv="content-type" content="text/html; charset=UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="shortcut icon" href="assets/favicon.ico" type="image/x-icon"/>

    <link rel="stylesheet" href="assets/general/css/default.css">
    <link rel="stylesheet" href="assets/general/css/layout.css">

    <title><?= $title ?></title>
</head>
<body>
<div id="wrapper">
    <div id="header">
        <!--Nav-->
        <!--        --><?php
        //var_dump($routes) ?>
        <div id="nav" class="clearfix">
            <span id="brand-name-span">Slim Example Project</span>
            <?php
            foreach ($routes as $name => $route) {
                echo '<a href="' . $route['link'] . '" ' . ($route['active'] ? 'class="is-active"' : '') . ' data-active-color="' . $route['color'] . '">' . $name . '</a>';
            }
            ?>

            <div id="nav-icon">
                <span></span>
                <span></span>
                <span></span>
                <span></span>
            </div>
            <span class="nav-indicator" id="nav-indicator"></span>
        </div>
    </div>

    <div id="pageContent">

        <?= $content ?>
    </div>

    <div id="footer">

    </div>
</div>

<script src="assets/general/js/layout.js"></script>
</body>
</html>

