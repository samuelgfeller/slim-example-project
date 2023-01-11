function initAlertModalEventListeners() {
    // Event delegation. Add event listeners to non-existent elements during page loads but loaded dynamically
// more on https://stackoverflow.com/a/34896387/9013718
    document.addEventListener('click', function (e) {
        if (e.target && (
            // When anywhere in the window is clicked except the modal area itself
            e.target === document.getElementById('alert-modal') ||
            // Hide modal when anywhere in the window is clicked except the modal area itself
            e.target === document.getElementById('alert-modal-cancel-btn') ||
            // When clicking the confirmation button modal box has to disappear too
            e.target === document.getElementById('alert-modal-confirm-btn')
        )) {
            closeAlertModal();
        }
    });

}

/**
 * Create and show alert modal with given content
 *
 * @param {string} title
 * @param {string} info
 * @param {function} confirmationEventFunction function that is executed on confirmation
 * @param {string} btnString
 */
export function createAlertModal(title, info, confirmationEventFunction, btnString = 'Yes delete') {
    initAlertModalEventListeners();
    // Insert parts into entire modal structure
    let htmlString = '<div id="alert-modal">' +
        '<div id="alert-modal-box">' +
        '<div id="alert-modal-icon">' +
        // '<img src="assets/general/img/flash-warning.svg" alt="warning">' +
        '</div>' +
        '<div id="alert-modal-body">' +
        '<h3>' + title + '</h3>' +
        '<p>' + info + '</p>' +
        '</div>' +
        '<div id="alert-modal-footer">' +
        '<button class="btn modal-btn" id="alert-modal-cancel-btn">Cancel</button>' +
        '<button class="btn modal-btn btn-red" id="alert-modal-confirm-btn">' + btnString + '</button>' +
        '</div>' +
        '</div>' +
        '</div>';
    // Insert at end of page content which is in <main></main>
    document.getElementsByTagName('main')[0].insertAdjacentHTML('beforeend', htmlString);

    // Add event listener on confirmation
    document.getElementById('alert-modal-confirm-btn').addEventListener('click', confirmationEventFunction);

    // Not working idk why
    document.getElementById('alert-modal-cancel-btn').focus();
}

function closeAlertModal() {
    document.getElementById('alert-modal').remove();
}