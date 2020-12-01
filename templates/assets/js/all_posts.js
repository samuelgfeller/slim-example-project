$(document).ready(function () {

    loadAllPosts();
    // Edit user open form modal
    $('#createPostBtn').on('click', function () {
        openCreatePostForm();
    });

    // Send update request
    $('#createPostDiv').on('click', '#submitBtnCreatePost', function () {
        submitCreatePost('createPostForm', 'all');
    });
});

/**
 * Populate #postsDiv with all posts in database
 */
function loadAllPosts() {
    //Load posts
    $.ajax({
        url: config.api_url+'posts',
        dataType: "json",
        type: 'get',
        beforeSend: function (xhr) {
            /* Authorization header */
            xhr.setRequestHeader("Authorization", "Bearer " + localStorage.getItem("token"));
        },
    }).done(function (output) {
        // output = JSON.parse(output);
        if (output !== '') {
            if (output === '[]') {
            } else {
                let posts = output;
                // let places = JSON.parse(output);
                $('#postsDiv').empty();
                $.each(posts, function (index, value) {
                    getPostBox(value).appendTo($('#postsDiv'));
                });
            }
        } else {
            console.log(output);
        }
    }).fail(function (xhr) {
        $('#postsDiv').empty();
        handleFail(xhr);
    });
}

/**
 * Get Post box for all users without rights
 *
 * @param jsonData
 * @returns {jQuery|HTMLElement}
 */
function getPostBox(jsonData){
    return $('<div class="singleBox" id="post' + jsonData.id + '">' +
        '<div class="boxContent">' +
        '<div class="loader" id="loaderForPost'+jsonData.id+'"></div>'+
        '<h3 class="boxHeader">' + jsonData.user_name + '</h3>' +
        '<div id="boxInnerContent'+jsonData.id+'">' +
        '<p><span class="infoInBoxSpan"></span><b>' + jsonData.message + '</b></p>' +
        '<p><span class="infoInBoxSpan">Updated at: </span><b>' + jsonData.updated_at + '</b></p>' +
        '<p><span class="infoInBoxSpan">Created at: </span>' + jsonData.created_at + '</p>' +
        '</div>' +
        '</div>' +
        '</div>');
}