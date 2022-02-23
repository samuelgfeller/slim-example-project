// Open modal box to create new post after click on plus button
document.getElementById('create-post-btn').addEventListener('click', function () {
    createPostModal();
});

// Event delegation (event listeners on dynamically loaded elements)
document.addEventListener('click', function (e) {
    // Submit form
    if (e.target && e.target.id === 'submit-btn-create-post') {
        submitCreatePost();
    }
});

/**
 * Create and display modal box to create a new post
 */
function createPostModal() {
    let header = '<h2>Post</h2>';
    let body = '<div id="create-post-form" class="form modal-form">' +
        '<textarea rows="4" cols="50" name="message" id="create-message-textarea" class="form-textarea" ' +
        'placeholder="Your message here." minlength="4" maxlength="500" required></textarea>' +
        '</div>';
    let footer = '<button type="button" id="submit-btn-create-post" class="submit-btn modal-submit-btn">Create post</button>' +
        '<div class="clearfix"></div>' +
        '</div>';
    let container = document.getElementById('create-post-div');
    createModal(header, body, footer, container);
}

/**
 * Send post creation to server
 *
 * @param formId
 */
function submitCreatePost(formId) {
    // 'own' if own posts should be loaded after creation or 'all' if all should
    let postVisibilityScope = document.getElementById('post-wrapper').dataset.postVisibilityScope;

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