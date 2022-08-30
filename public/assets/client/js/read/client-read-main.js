import {basePath} from "../../../general/js/config.js";

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
    // To display the checkmark loader only when the user expects that his content is saved we have to know if he/she is
    // still typing. Otherwise, the callback of the "old" ajax request to save shows the checkmark loader when it's done
    let userStillTyping = false;
    let textareaInputPauseTimeoutId;
    for (let textarea of activityTextareas) {
        textarea.addEventListener('click', function (e) {
            this.removeAttribute('readonly');
        });
        textarea.addEventListener('input', function () {
            userStillTyping = true;
            // Hide loader if there was one
            hideCheckmarkLoader(this.parentNode.querySelector('.circle-loader'));
            // Only save if 1 second writing pause
            clearTimeout(textareaInputPauseTimeoutId);
            textareaInputPauseTimeoutId = setTimeout(function () {
                // Runs 1 second (1000 ms) after the last change
                saveToDb.call(textarea);
            }, 1000);
        });
        // textarea.addEventListener('change', saveToDb, false)
    }

    let circleLoaderTimeoutId;

    function saveToDb() {
        // Setting the var to false, to compare it on success. If it is not false anymore, it means that the user typed
        userStillTyping = false;
        // show circle loader
        let noteId = this.parentNode.dataset.noteId;
        // By using querySelector on the targeted textarea parent it's certain that the right circleLoader is targeted
        let circleLoader = this.parentNode.querySelector('.circle-loader');
        circleLoader.style.display = 'inline-block';

        // Make ajax call
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
                    let textStatus = JSON.parse(xHttp.responseText).status;
                    if (userStillTyping === false) {
                        // Show checkmark only on status success and if user is not typing
                        if (textStatus === 'success') {
                            // Show checkmark in loader
                            circleLoader.classList.add('load-complete');
                            circleLoader.querySelector('.checkmark').style.display = 'block';

                            // Remove checkmark after 1 sec
                            setTimeout(function () {
                                // Hide circle loader and its child the checkmark
                                // circleLoader.style.animation = 'loader-spin 1.2s infinite linear';
                                hideCheckmarkLoader(circleLoader);
                            }, 3000);
                        }else{
                            hideCheckmarkLoader(circleLoader);
                        }
                    }

                }
            }
        };

        xHttp.open('PUT', basePath + 'notes' + '/' + noteId, true);
        xHttp.setRequestHeader("Content-type", "application/json");

        // Data format: "fname=Henry&lname=Ford"
        // In [square brackets] to be evaluated
        xHttp.send(JSON.stringify({[this.name]: this.value})); // this is the textarea
    }

    function hideCheckmarkLoader(checkmarkLoader) {
        checkmarkLoader.classList.remove('load-complete');
        checkmarkLoader.querySelector('.checkmark').style.display = 'none';
        checkmarkLoader.style.display = 'none';
    }
}

