$(document).ready(function () {

/*
    if (window.location.href.indexOf("franky") > -1) {
        alert("your url contains the name franky");
    }
*/
    $('#userListNavBtn').on('click', function () {
        // location.href = config.frontend_url + 'user-list';
        location.href = config.frontend_url + 'pages/userlist.html';

    });
    $('#profileNavBtn').on('click', function () {
        // location.href = config.frontend_url + 'profile';
        location.href = config.frontend_url + 'pages/profile.html';

    });
    $('#loginNavBtn').on('click', function () {
        // location.href = config.frontend_url + 'login';
        location.href = config.frontend_url + 'pages/login.twig';

    });
    $('#registerNavBtn').on('click', function () {
        // location.href = config.frontend_url + 'register';
        location.href = config.frontend_url + 'pages/register.html';

    });
    $('#ownPostsNavBtn').on('click', function () {
        // location.href = config.frontend_url + 'own-posts';
        location.href = config.frontend_url + 'pages/own-posts.html';

    });
    $('#allPostsNavBtn').on('click', function () {
        // location.href = config.frontend_url + 'posts';
        location.href = config.frontend_url + 'pages/all-posts.html';
    });

});

function redirectDefaultPage() {
    location.href = config.frontend_url + 'pages/login.twig';
}