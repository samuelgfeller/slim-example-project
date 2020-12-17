<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="Content-Type" content="text/html;charset=UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <link rel="shortcut icon" href="/assets/favicon.ico" type="image/x-icon"/>

    <link rel="stylesheet" href="/assets/general/css/default.css">
    <link rel="stylesheet" href="/assets/general/css/layout.css">

    <title><?= $title ?></title>
</head>
<body>
<div class="wrapper">
    <div id="header">
        <!--Nav-->
        <div class="nav" id="nav">
            <a href="/" class="active" data-active-color="green">Home</a>
            <a href="users" data-active-color="blue">Users</a>
            <a href="profile" data-active-color="orange">Profile</a>
            <a href="own-posts" data-active-color="red">Own posts</a>
            <a href="posts" data-active-color="yellow">Posts</a>
            <a href="login" data-active-color="green">Login</a>
            <a href="register" data-active-color="green">Register</a>
            <a href="javascript:void(0);" class="icon" id="toggleMenuBtn">
                <i class="fa fa-bars"></i>
            </a>
            <span class="nav-indicator"></span>
        </div>
    </div>

    <div id="pageContent">

        <?= $content ?>
    </div>

    <div id="footer">

    </div>
</div>

<script src="/assets/general/js/layout.js"></script>
</body>
</html>

