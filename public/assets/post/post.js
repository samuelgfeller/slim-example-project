// Global variables
// Get basepath. Especially useful when developing on localhost/project-name
const basePath = document.getElementsByTagName('base')[0].getAttribute('href');

// Load posts on
loadPosts();

// Open modal box to create new post after click on plus button
document.getElementById('create-post-btn').addEventListener('click', function () {
    createPostModal();
});

// Event delegation (event listeners on dynamically loaded elements)
document.addEventListener('click', function (e) {
    // Submit form on create button click
    if (e.target && e.target.id === 'submit-btn-create-post') {
        submitCreatePost();
    }
    // Open edit post modal after edit button click in post box
    if (e.target && e.target.className.includes('card-edit-icon')) {
        let postId = e.target.dataset.id;
        updatePostModal(postId);
    }
    // Submit edit post
    if (e.target && e.target.id === 'submit-btn-update-post') {
        let postId = e.target.dataset.id;
        submitUpdatePost(postId);
    }
    // Submit delete post
    if (e.target && e.target.className.includes('card-del-icon')) {
        let postId = e.target.dataset.id;
        submitDeletePost(postId);
    }
});

/**
 * Load posts into dom
 */
function loadPosts() {
    displayPostContentPlaceholder();
    // 'own' if own posts should be loaded after creation or 'all' if all should
    let postVisibilityScope = document.getElementById('post-wrapper').dataset.postVisibilityScope;
    let queryParams = postVisibilityScope === 'own' ? '?user=session' : '';

    let xHttp = new XMLHttpRequest();
    xHttp.onreadystatechange = function () {
        if (xHttp.readyState === XMLHttpRequest.DONE) {
            // Fail
            if (xHttp.status !== 200) {
                // Default fail handler
                handleFail(xHttp);
            }
            // If status code 401 user is not logged in
            if (xHttp.status === 401) {
                removeContentPlaceholder();
                document.getElementById('post-wrapper').insertAdjacentHTML('afterend',
                    '<p>Please <a href="' + JSON.parse(xHttp.responseText).loginUrl +
                    '">login</a> to access your posts.</p>');
            }
            // Success
            else {
                let posts = JSON.parse(xHttp.responseText);
                removeContentPlaceholder();
                addPostsToDom(posts);
            }
        }
    };

    // For GET requests, query params have to be passed in the url directly. They are ignored in send()
    xHttp.open('GET', basePath + 'posts' + queryParams, true);
    xHttp.setRequestHeader("Content-type", "application/json");

    xHttp.send();
}

/**
 * Display post content placeholders
 */
function displayPostContentPlaceholder() {
    let postWrapper = document.getElementById('post-wrapper');
    // Empty posts
    postWrapper.innerHTML = '';

    let contentPlaceholderHtml =
        '<div class="preloading-card">' +
        '    <div class="preloading-card-header">' +
        '        <div class="load-wrapper">' +
        '            <div class="activity"></div>' +
        '        </div>' +
        '    </div>' +
        '    <div class="preloading-card-body">' +
        '        <div class="load-wrapper">' +
        '            <div class="activity"></div>' +
        '        </div>' +
        '    </div>' +
        '</div>';

    // Add content placeholder 3 times
    postWrapper.insertAdjacentHTML('beforeend', contentPlaceholderHtml);
    postWrapper.insertAdjacentHTML('beforeend', contentPlaceholderHtml);
    postWrapper.insertAdjacentHTML('beforeend', contentPlaceholderHtml);
}

/**
 * Remove placeholders
 */
function removeContentPlaceholder() {
    // I had a very strange issue. With getElementsByClassName I got 3 elements but only 2 seem to be looped through
    let contentPlaceholders = document.querySelectorAll('.preloading-card');
    // Foreach loop over content placeholders
    for (let contentPlaceholder of contentPlaceholders) {
        // remove from DOM
        contentPlaceholder.remove();
    }
}

/**
 * Add post to page
 *
 * @param {object[]} posts
 */
function addPostsToDom(posts) {
    let postContainer = document.getElementById('post-wrapper');

    // If no results, tell user so
    if (posts.length === 0) {
        postContainer.insertAdjacentHTML('afterend', '<p>No posts could be found.</p>')
    }

    // Loop over posts and add to DOM
    for (const post of posts) {
        // Set delete and edit buttons but only if user is viewing its own posts
        let ownPostsButtons = '';
        if (post.userMutationRight === 'all') {
            ownPostsButtons = '<img src="assets/general/img/edit_icon.svg" class="card-edit-icon cursor-pointer" ' +
                'data-id="' + post.postId + '" alt="edit">' +
                '<img src="assets/general/img/del_icon.svg" class="card-del-icon cursor-pointer" ' +
                'data-id="' + post.postId + '" alt="del">';
        }
        // Post card HTML
        let postHtml = '<div class="post-card" id="post' + post.postId + '">' +
            '    <div class="card-content">' +
            ownPostsButtons +
            '        <h3 class="card-header">' + post.userName + '</h3>' +
            '        <div id="card-inner-content' + post.postId + '">' +
            '            <p class="display-newlines"><b>' + post.postMessage + '</b></p>' +
            '            <p class="post-card-additional-info">Updated: ' +
            '               <span class="layout-color-text">' + post.postUpdatedAt + '</span><br>' +
            '            Created: <span class="layout-color-text">' + post.postCreatedAt + '</span></p>' +
            '        </div>' +
            '    </div>' +
            '</div>';

        // Add to DOM
        postContainer.insertAdjacentHTML('beforeend', postHtml);
    }
}

/**
 * Create and display modal box to create a new post
 */
function createPostModal() {
    let header = '<h2>Post</h2>';
    let body = '<div class="form modal-form">' + '<textarea rows="4" cols="50" name="message" ' +
        'id="create-message-textarea" class="form-input" ' + 'placeholder="Your message here." minlength="4" ' +
        'maxlength="500" required></textarea>' + '</div>';
    let footer = '<button type="button" id="submit-btn-create-post" class="submit-btn modal-submit-btn">' +
        'Create post</button>' + '<div class="clearfix"></div>' + '</div>';
    document.getElementById('post-wrapper').insertAdjacentHTML('afterend', '<div id="create-post-div"></div>');
    let container = document.getElementById('create-post-div');
    createModal(header, body, footer, container);
}

/**
 * Send post creation to server
 *
 * @param formId
 */
function submitCreatePost(formId) {
    // Check if textarea content is valid (frontend validation)
    let textArea = document.getElementById('create-message-textarea')
    if (textArea.checkValidity() === false) {
        // If not valid, report to user and return void
        textArea.reportValidity();
        return;
    }

    // Show loader to indicate user that the request is on its way
    showPostModalLoader();

    let xHttp = new XMLHttpRequest();
    xHttp.onreadystatechange = function () {
        if (xHttp.readyState === XMLHttpRequest.DONE) {
            // Fail
            if (xHttp.status !== 201 && xHttp.status !== 200) {
                // Default fail handler
                handleFail(xHttp);
            }
            // Success
            else {
                closeModal();
                loadPosts();

                // Hide loader
                hidePostModalLoader();
            }
        }
    };

    xHttp.open('POST', basePath + 'posts', true);
    xHttp.setRequestHeader("Content-type", "application/json");

    // Data format: "fname=Henry&lname=Ford"
    // In [square brackets] to be evaluated
    xHttp.send(JSON.stringify({[textArea.name]: textArea.value}));
}

/**
 * Show post modal loader
 */
function showPostModalLoader() {
    document.getElementById('modal-footer').insertAdjacentHTML('afterbegin',
        '<div class="lds-ellipsis"><div></div><div></div><div></div><div></div></div>');
}

/**
 * Hide post modal loader
 */
function hidePostModalLoader() {
    document.getElementsByClassName('lds-ellipsis')[0].remove();
}

/**
 * After the click on the edit icon of a post, a modal box is opened
 * with an editable textarea containing the most recent content (request to server)
 *
 * @param {string} postId
 */
function updatePostModal(postId) {

    let header = '<h2>Edit post</h2>';
    let body = '<div class="form modal-form"><textarea rows="4" cols="50" name="message" ' +
        'id="update-message-textarea" class="form-input" minlength="4" ' +
        'maxlength="500" required disabled>Loading...</textarea></div>';
    let footer = '<button type="button" id="submit-btn-update-post" class="submit-btn modal-submit-btn">' +
        'Update post</button><div class="clearfix"></div>';

    document.getElementById('post-wrapper').insertAdjacentHTML(
        'afterend', '<div id="update-post-modal"></div>');
    let container = document.getElementById('update-post-modal');

    createModal(header, body, footer, container);

    // Retrieve actual post infos via Ajax and populate textarea
    let xHttp = new XMLHttpRequest();
    xHttp.onreadystatechange = function () {
        if (xHttp.readyState === XMLHttpRequest.DONE) {
            // Fail
            if (xHttp.status !== 200) {
                // Default fail handler
                handleFail(xHttp);
            }
            // Success
            else {
                let output = JSON.parse(xHttp.responseText);
                let updateMessageTextarea = document.getElementById('update-message-textarea');
                updateMessageTextarea.value = output.message;
                updateMessageTextarea.disabled = false;
                // Set post id on submit button as its easiest to retrieve on delegated event listener
                document.getElementById('submit-btn-update-post').setAttribute('data-id', output.id);
            }
        }
    };

    // Read post infos
    xHttp.open('GET', basePath + 'posts/' + postId, true);
    xHttp.setRequestHeader("Content-type", "application/json");
    xHttp.send();
}

/**
 * Submit post change
 *
 * @param {string} postId
 */
function submitUpdatePost(postId) {
    let updateMessageTextarea = document.getElementById('update-message-textarea');

    // Show loader to indicate user that the request is on its way
    showPostModalLoader();

    // Ajax request
    let xHttp = new XMLHttpRequest();
    xHttp.onreadystatechange = function () {
        if (xHttp.readyState === XMLHttpRequest.DONE) {
            // Fail
            if (xHttp.status !== 200) {
                // Default fail handler
                handleFail(xHttp);
            }
            // Success
            else {
                hidePostModalLoader();
                closeModal();
                loadPosts();
            }
        }
    };

    // Read post infos
    xHttp.open('PUT', basePath + 'posts/' + postId, true);
    xHttp.setRequestHeader("Content-type", "application/json");
    // In square brackets to be evaluated
    xHttp.send(JSON.stringify({[updateMessageTextarea.name]: updateMessageTextarea.value}));
}

/**
 * Submit post deletion
 *
 * @param {string} postId
 */
function submitDeletePost(postId) {

    // Replace delete icon with loader to indicate user that the request is on its way
    showPostDeleteLoader(postId);

    // Ajax request
    let xHttp = new XMLHttpRequest();
    xHttp.onreadystatechange = function () {
        if (xHttp.readyState === XMLHttpRequest.DONE) {
            // Not logged in, redirect to login url
            if (xHttp.status === 401) {
                window.location.href = JSON.parse(xHttp.responseText).loginUrl;
            }
            // Fail
            if (xHttp.status !== 200) {
                // Default fail handler
                handleFail(xHttp);
                // Reload posts on fail as issue may be resolved with refresh
                loadPosts();
            }
            // Success
            else {
                // Remove post card after successful deletion
                document.getElementById('post' + postId).remove();
            }

            // After request is done, reset loader size no matter if success or failure
            document.documentElement.style.setProperty('--three-dots-loader-factor', '0.65');
        }
    };

    // Read post infos
    xHttp.open('DELETE', basePath + 'posts/' + postId, true);
    // Important to add content type json and "Redirect-to-if-unauthorized" header for the UserAuthenticationMiddleware
    // to know to send the login url in the json response body and where to redirect back after a successful login
    xHttp.setRequestHeader("Content-type", "application/json");
    xHttp.setRequestHeader("Redirect-to-if-unauthorized", "post-list-own-page");

    xHttp.send();
}

/**
 * Replace delete icon with loader to indicate user that the request is on its way
 */
function showPostDeleteLoader(postId) {
    // Insert loader
    document.querySelector('.card-edit-icon[data-id="' + postId + '"]').insertAdjacentHTML('afterend',
        '<div class="lds-ellipsis post-box-del-loader"><div></div><div></div><div></div><div></div></div>');
    // Change loader size in changing the css variable
    document.documentElement.style.setProperty('--three-dots-loader-factor', '0.5');
    // Hide delete icon
    document.querySelector('.box-del-icon[data-id="' + postId + '"]').style.display = 'none';
    // Lower post box opacity to reinforce feeling that something is happening
    document.getElementById('post' + postId).style.opacity = '0.6';
}
