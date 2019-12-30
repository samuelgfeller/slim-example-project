
$(document).ready(function () {

    loadAllPosts();
    // Edit user open form modal
    $('#createPostBtn').on('click', function () {
        openCreatePostForm();
    });

    // Send update request
    $('#createPostDiv').on('click', '#submitBtnCreatePost', function () {
        submitCreatePost();
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
            xhr.setRequestHeader("Authorization", "Bearer " + sessionStorage.getItem("token"));
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
            alert(xhr.responseText);
    });
}

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

/**
 * Open modal to create a post
 */
function openCreatePostForm() {
    let header = '<h2>Post</h2>';
    let body = '<form action="posts" class="blueForm modalForm" method="post" autocomplete="on">' +
        '<textarea rows="4" cols="50" name="message" id="createMessageTextarea" placeholder="Your message here." minlength="4" maxlength="500" required></textarea>';
    let footer = '<button type="button" id="submitBtnCreatePost" class="submitBtn modalSubmitBtn">Create post</button>' +
        '<div class="clearfix"></div>' +
        '</form>';
    createModal(header,body,footer,$('#createPostDiv'));
}

function submitCreatePost() {
    $.ajax({
        url: config.api_url+'posts',
        type: 'post',
        dataType: "json",
        contentType: "application/json; charset=utf-8",
        beforeSend: function (xhr) {
            /* Authorization header */
            xhr.setRequestHeader("Authorization", "Bearer " + sessionStorage.getItem("token"));
        },
        data: JSON.stringify({
            message: $('#createMessageTextarea').val(),
            user : sessionStorage.getItem("token"),
        }),
    }).done(function (output) {
        closeModal();
        if (output.success === true || output.success === 'true') {
            loadAllPosts();
        } else {
            console.log(output.success);
        }
    }).fail(function (output) {
        console.log(output);
        alert('Error while adding');
    });
}

