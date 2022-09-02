import {basePath} from "../../../general/js/config.js";

export function saveStatusChange() {
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
                // Show checkmark only on status success and if user is not typing
                if (textStatus === 'success') {
                    console.log('Client status id changed');
                }
            }
        }
    };
    let clientId = document.getElementById('client-id').value;
    xHttp.open('PUT', basePath + 'clients' + '/' + clientId, true);
    xHttp.setRequestHeader("Content-type", "application/json");

    // Data format: "fname=Henry&lname=Ford"
    // In [square brackets] to be evaluated
    xHttp.send(JSON.stringify({
        // this is the textarea
        [this.name]: this.value,
    }));
}