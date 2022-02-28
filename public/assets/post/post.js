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
});

/**
 * Load posts into dom
 */
function loadPosts() {
    displayPostContentPlaceholder();
    // 'own' if own posts should be loaded after creation or 'all' if all should
    let postVisibilityScope = document.getElementById('post-wrapper').dataset.postVisibilityScope;
    let queryParams = postVisibilityScope === 'own' ? '?scope=own' : '';

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
    // Get basepath. Especially useful when developing on localhost/project-name
    let basePath = document.getElementsByTagName('base')[0].getAttribute('href');

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
        '<div class="preloading-box-content">' +
        '    <div class="preloading-box-header">' +
        '        <div class="load-wrapper">' +
        '            <div class="activity"></div>' +
        '        </div>' +
        '    </div>' +
        '    <div class="preloading-box-inner-content">' +
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
    let contentPlaceholders = document.querySelectorAll('.preloading-box-content');
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
        let postHtml = '<div class="post-squares" id="post' + post.postId + '">' +
            '    <div class="box-content">' +
            '        <div class="loader" id="loaderForPost' + post.postId + '"></div>' +
            '        <h3 class="box-header">' + post.userName + '</h3>' +
            '        <div id="box-inner-content' + post.postId + '">' +
            '            <p><span class="info-in-box-span"></span><b>' + post.postMessage + '</b></p>' +
            '            <p><span class="info-in-box-span">Updated at: </span><b>' + post.postUpdatedAt + '</b></p>' +
            '            <p><span class="info-in-box-span">Created at: </span>' + post.postCreatedAt + '</p>' +
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
    let body = '<div id="create-post-form" class="form modal-form">' + '<textarea rows="4" cols="50" name="message" ' +
        'id="create-message-textarea" class="form-textarea" ' + 'placeholder="Your message here." minlength="4" ' +
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
                document.getElementsByClassName('lds-ellipsis')[0].remove();
            }
        }
    };
    // Get basepath. Especially useful when developing on localhost/project-name
    let basePath = document.getElementsByTagName('base')[0].getAttribute('href');

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