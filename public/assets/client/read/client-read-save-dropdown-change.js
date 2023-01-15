import {basePath} from "../../general/general-js/config.js?v=0.2.0";
import {handleFail} from "../../general/ajax/ajax-util/fail-handler.js?v=0.2.0";

export function saveClientReadDropdownChange() {
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
                    this.style.boxShadow = '0 0px 8px #2e3e5075';
                    setTimeout(function () {
                        this.style.boxShadow = 'none';
                    }.bind(this), 700);
                }
            }
        }
    }.bind(this); // Add this context (textarea) to callback
    let clientId = document.getElementById('client-id').value;
    xHttp.open('PUT', basePath + 'clients' + '/' + clientId, true);
    xHttp.setRequestHeader("Content-type", "application/json");
    xHttp.setRequestHeader("Redirect-to-url-if-unauthorized", basePath + "clients/" + clientId);

    // Data format: "fname=Henry&lname=Ford"
    // In [square brackets] to be evaluated
    xHttp.send(JSON.stringify({
    // this is the textarea
        [this.name]: this.value,
    }));
}