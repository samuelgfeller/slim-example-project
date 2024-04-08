// Event delegation. Add event listeners to non-existent elements during page loads but loaded dynamically
// https://stackoverflow.com/a/34896387/9013718
document.addEventListener('click', function (e) {
    // Hide modal when close-modal button is clicked
    if (e.target && e.target.id === 'close-modal') {
        closeModal();
    }
});
// Hide modal only if user did both mousedown and mouseup outside the modal. This is to prevent
// the modal closing when doing mousedown to select text from an input field and going off the modal border
document.addEventListener('mousedown', function (e) {
    if (e.target && e.target === document.getElementById('modal')) {
        document.addEventListener('mouseup', function mouseUpListener(e) {
            // Hide modal when anywhere in the window is clicked except the modal area itself
            closeModal();
            document.removeEventListener('mouseup', mouseUpListener);
        });
    }
});

// Hide modal when the escape key is pressed
document.addEventListener('keydown', function (e) {
    if (e.key === 'Escape') {
        closeModal();
    }
});

/**
 * Create and show modal with given content
 *
 * @param {string} header
 * @param {string} body
 * @param {string} footer
 * @param {object} container HTML object
 * @param {boolean} wideModal when modal contains two rows it is wider especially when shrinking screen
 */
export function createModal(header, body, footer, container, wideModal = false) {
    // Insert parts into entire modal structure
    // '<div  id="modal-container">' +
    let htmlString = `<div id="modal">
<div id="modal-box" class="${wideModal === true ? 'wide-modal' : ''}">
<div id="modal-header"><span id="close-modal">&times;</span>${header}</div>
<div id="modal-body">${body}</div>
<div id="modal-footer">${footer}</div>
</div></div>`;
    // Insert at end of page content which is in <main></main>
    document.getElementsByTagName('main')[0].insertAdjacentHTML('beforebegin', htmlString);
}

export function closeModal() {
    document.getElementById('modal').remove();
}