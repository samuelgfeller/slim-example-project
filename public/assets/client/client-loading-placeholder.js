
/**
 * Display client content placeholders
 */
function displayClientContentPlaceholder() {
    let clientWrapper = document.getElementById('client-wrapper');
    // Empty clients
    clientWrapper.innerHTML = '';

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
    clientWrapper.insertAdjacentHTML('beforeend', contentPlaceholderHtml);
    clientWrapper.insertAdjacentHTML('beforeend', contentPlaceholderHtml);
    clientWrapper.insertAdjacentHTML('beforeend', contentPlaceholderHtml);
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