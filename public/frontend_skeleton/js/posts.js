

/**
 * Open modal to create a post
 */
function openCreatePostForm() {
    let header = '<h2>Post</h2>';
    let body = '<form action="posts" id="createPostForm" class="blueForm modalForm" method="post" autocomplete="on">' +
        '<textarea rows="4" cols="50" name="message" id="createMessageTextarea" placeholder="Your message here." minlength="4" maxlength="500" required></textarea>';
    let footer = '<button type="button" id="submitBtnCreatePost" class="submitBtn modalSubmitBtn">Create post</button>' +
        '<div class="clearfix"></div>' +
        '</form>';
    createModal(header,body,footer,$('#createPostDiv'));
}

/**
 * Send post creation to server
 *
 * @param formId
 * @param scope 'own' if own posts should be loaded after creation or 'all' if all should
 */
function submitCreatePost(formId, scope) {
    if (formIsValid(formId)) {
        $.ajax({
            url: config.api_url + 'posts',
            type: 'post',
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
                if (scope === 'own'){
                    loadAllOwnPosts();
                }
                if (scope === 'all'){
                    loadAllPosts();
                }
            }
        }).fail(function (xhr) {
            handleFail(xhr);
        });
    }
}

