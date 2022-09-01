import {basePath} from "../../../general/js/config.js";
import {changeUserIsTyping, hideCheckmarkLoader, userIsTyping} from "./text-area-event-listener-setup.js";

export function saveNoteChangeToDb() {
    // Setting the var to false, to compare it on success. If it is not false anymore, it means that the user typed
    changeUserIsTyping(false);
    // show circle loader
    let noteId = this.dataset.noteId;
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
                if (userIsTyping === false) {
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
                        // Hide checkmark loader "cleanly" so that it's not broken on the next input
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