
$(document).ready(function () {

    loadAllOwnPosts();
    // Edit user open form modal
    $('#createPostBtn').on('click', function () {
        openCreatePostForm();
    });

    // Send update request
    $('#createPostDiv').on('click', '#submitBtnCreatePost', function () {
        submitCreatePost('createPostForm', 'own');
    });

    // Edit post open form modal
    $('#postsDiv').on('click', '.editIcon', function () {
        let id = $(this).data('id');
        openEditPostForm(id);
    });

    // Send update request
    $('#postsDiv').on('click', '#submitBtnEditPost', function () {
        let id = $(this).data('id');
        submitUpdatedPost(id, 'updatePostForm');
    });

    // Delete post
    $('#postsDiv').on('click', '.delIcon', function () {
        let id = $(this).data('id');
        deletePost(id);
    });
});


/**
 * Populate #postsDiv with all posts in database
 */
function loadAllOwnPosts() {
    // https://florimond.dev/blog/articles/2018/08/restful-api-design-13-best-practices-to-make-your-users-happy/

    //Load posts
    $.ajax({
        url: config.api_url+'own-posts',
        // crossDomain: true,
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
                    getOwnPostBox(value).appendTo($('#postsDiv'));
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
 * Load own post box with edit and delete button
 *
 * @param jsonData
 * @returns {jQuery|HTMLElement}
 */
function getOwnPostBox(jsonData){
    return $('<div class="singleBox" id="post' + jsonData.id + '">' +
        '<div class="boxContent">' +
        '<img src="/img/edit_icon.svg" class="editIcon cursorPointer" data-id="' + jsonData.id + '" alt="edit">' +
        '<img src="/img/del_icon.svg" class="delIcon cursorPointer" data-id="' + jsonData.id + '" alt="del">' +
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
 * Open Modalbox with form to edit the post data
 *
 * @param id
 */
function openEditPostForm(id) {
    let header = '<h2>Edit post</h2>';
    let body = '<form action="posts/' + id + '" id="updatePostForm" class="blueForm modalForm" autocomplete="on">' +
        '<textarea name="message" id="createMessageTextarea" placeholder="Loading..." minlength="4" maxlength="500" required></textarea>';
    let footer = '<button type="button" id="submitBtnEditPost" data-id="" class="submitBtn modalSubmitBtn">Update post</button>' +
        '<div class="clearfix"></div>' +
        '</form>';

    createModal(header, body, footer, $('#postsDiv'));

    // Retrieve actual post infos and populate input
    $.ajax({
        dataType: "json",
        url: config.api_url+'posts/' + id,
        type: 'get',
        beforeSend: function (xhr) {
            /* Authorization header */
            xhr.setRequestHeader("Authorization", "Bearer " + localStorage.getItem("token"));
        },
    }).done(function (output) {
        let post = output;
        $('#createMessageTextarea').val(post.message);
        $('#submitBtnEditPost').attr('data-id', post.id);
    }).fail(function (xhr) {
        closeModal();

        handleFail(xhr);
    });
}

/**
 * Send form data via put to update a post
 *
 * @param id
 * @param formId
 */
function submitUpdatedPost(id, formId) {
    if (formIsValid(formId)) {
        $.ajax({
            url: config.api_url + 'posts/' + id,
            // url: 'posts',
            type: 'put',
            dataType: "json",
            contentType: "application/json; charset=utf-8",
            beforeSend: function (xhr) {
                /* Authorization header */
                xhr.setRequestHeader("Authorization", "Bearer " + localStorage.getItem("token"));
            },
            data: JSON.stringify({
                message: $('#createMessageTextarea').val(),
                user: localStorage.getItem("token"),
            }),
        }).done(function (output) {
            closeModal();
            if (output.status === 'success') {
                showLoader('loaderForPost' + id);
                reloadPost(id);
            } else {
                console.log(output);
            }
        }).fail(function (xhr) {
            handleFail(xhr);
        });
    }
}

/**
 * Send request to delete a post
 *
 * @param id
 */
function deletePost(id) {
    if (confirm('Are you sure that you want to delete this post?')) {
        $.ajax({
            url: config.api_url+'posts/' + id,
            type: 'delete',
            beforeSend: function (xhr) {
                /* Authorization header */
                xhr.setRequestHeader("Authorization", "Bearer " + localStorage.getItem("token"));
            },
        }).done(function (output) {
            if (output.status === 'success') {
                $('#post' + id).remove();
            } else {
                alert('Error while deleting');
            }
        }).fail(function (xhr) {
            handleFail(xhr);
        });
    }
}

function reloadPost(id){
    $.ajax({
        dataType: "json",
        url: config.api_url+'posts/' + id,
        type: 'get',
        beforeSend: function (xhr) {
            /* Authorization header */
            xhr.setRequestHeader("Authorization", "Bearer " + localStorage.getItem("token"));
        },
    }).done(function (output) {
        let post = output;
        hideLoader('loaderForPost'+id);
        $('#post'+id).replaceWith(getOwnPostBox(post))
    }).fail(function (xhr) {
        handleFail(xhr);
    });
}
