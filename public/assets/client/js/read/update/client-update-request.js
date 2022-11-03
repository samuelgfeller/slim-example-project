import {handleFail} from "../../../../general/js/requests/fail-handler.js";
import {hideCheckmarkLoader, userIsTypingOnNoteId} from "../client-read-text-area-event-listener-setup.js";
import {basePath} from "../../../../general/js/config.js";
import {createFlashMessage} from "../../../../general/js/requests/flash-message.js";

export function submitClientUpdate(field, value){
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
                createFlashMessage('success', field.replace('_', ' ') + ' was updated.');
            }
        }
    };
    let clientId = document.getElementById('client-id').value;
    xHttp.open('PUT', basePath + 'clients' + '/' + clientId, true);
    xHttp.setRequestHeader("Content-type", "application/json");
    xHttp.setRequestHeader("Redirect-to-url-if-unauthorized", basePath + "clients/" + clientId);

    // Data format: "fname=Henry&lname=Ford"
    // In square brackets to be evaluated
    xHttp.send(JSON.stringify({[field]: value}));
}