$(document).ready(function () {
    $('#userListNavBtn').on('click', function(){
        location.href = config.frontend_url+'userlist';

    });
    $('#profileNavBtn').on('click', function(){
        location.href = config.frontend_url+'profile';

    });
    $('#loginNavBtn').on('click', function(){
        location.href = config.frontend_url+'login';

    });
    $('#registerNavBtn').on('click', function(){
        location.href = config.frontend_url+'register';

    });

});