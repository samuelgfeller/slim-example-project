const clientStatus = document.querySelector('select[name="client_status"]');

clientStatus.addEventListener('change', function (e) {
    // Put selected option into select data attribute
    this.dataset.color = this.value;
    switch (this.innerText) {
        // case ''
    }
});

window.addEventListener("DOMContentLoaded", function (event) {
    initActivityTextareasEventListeners();
});

/**
 * Activity textareas should be editable on click and auto save on input pause
 */
function initActivityTextareasEventListeners() {
    let activityTextareas = document.querySelectorAll(
        '.client-activity-textarea-div textarea, #main-note-textarea-div textarea'
    );
    console.log(activityTextareas);
    let textareaChangeTimeoutId;
    for (let textarea of activityTextareas) {
        textarea.addEventListener('click', function (e) {
            this.removeAttribute('readonly');
        });
        textarea.addEventListener('input', function () {
            // Hide loader if there was one
            this.parentNode.querySelector('.circle-loader').style.display = 'none';
            // Only save if 1 second
            clearTimeout(textareaChangeTimeoutId);
            textareaChangeTimeoutId = setTimeout(function () {
                // Runs 1 second (1000 ms) after the last change
                saveToDb.call(textarea);
            }, 1000);
        });
        // textarea.addEventListener('change', saveToDb, false)
    }

    let circleLoaderTimeoutId;

    function saveToDb() {
        console.log('save to db');
        // show circle loader
        let noteId = this.dataset.noteId;
        // By using querySelector on the targeted textarea parent it's certain that the right circleLoader is targeted
        let circleLoader = this.parentNode.querySelector('.circle-loader');

        circleLoader.style.display = 'inline-block';

        // this.style.outline = '2px solid limegreen';
        circleLoaderTimeoutId = setTimeout(function () {
            circleLoader.classList.add('load-complete');
            circleLoader.querySelector('.checkmark').style.display = 'block';
            // this.style.outline = 'none';
            // Remove checkmark after 1 sec
            setTimeout(function () {
                // Hide circle loader and its child the checkmark
                // circleLoader.style.display = 'none';
            }, 3000);
        }.bind(this), 2000);

    }
}

