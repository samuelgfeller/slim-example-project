import {displayUserCardLoadingPlaceholder} from "./user-list-card-loading-placeholder.js";
import {basePath} from "../../general/js/config.js";
import {handleFail} from "../../general/js/requests/fail-handler.js";
import {getUserCardHtml} from "./user-list-card.html.js";

/**
 *  Load clients into DOM
 */
export function loadUsers() {
    displayUserCardLoadingPlaceholder();

    return new Promise(function (resolve, reject) {
        let xHttp = new XMLHttpRequest();
        xHttp.onreadystatechange = function () {
            if (xHttp.readyState === XMLHttpRequest.DONE) {
                // Fail
                if (xHttp.status !== 200) {
                    // Default fail handler
                    handleFail(xHttp);
                }
                // Success
                else {
                    // Resolve with json response
                    resolve(JSON.parse(xHttp.responseText));
                }
            }
        };

        // For GET requests, query params have to be passed in the url directly. They are ignored in send()
        xHttp.open('GET', basePath + 'users', true);
        xHttp.setRequestHeader("Content-type", "application/json");

        xHttp.send();
    });
}


/**
 * Add client to page
 *
 * @param {object[]} userResultDataArray
 * @param {object} statuses
 */
export function addUsersToDom(userResultDataArray, statuses) {
    let container = document.getElementById('user-wrapper');

    // If no results, tell user so
    if (userResultDataArray.length === 0) {
        container.insertAdjacentHTML('afterend', '<p>No users were found.</p>')
    }


    // Loop over users and add to DOM
    for (const userResult of userResultDataArray) {
        // Client card HTML
        let cardHtml = getUserCardHtml(container, userResult, statuses);

        // Add to DOM
        container.insertAdjacentHTML('beforeend', cardHtml);
    }
}