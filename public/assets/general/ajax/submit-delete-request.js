import {basePath} from "../general-js/config.js?v=0.4.0";
import {handleFail} from "./ajax-util/fail-handler.js?v=0.4.0";


/**
 * Send DELETE request.
 *
 * @param {string} route after base path
 * @param {boolean|string} redirectToRouteIfUnauthenticated true or redirect route url after base path.
 * If true, the redirect url is the same as the given route
 * @return Promise with as content server response as JSON
 */

export function submitDelete(route, redirectToRouteIfUnauthenticated = false) {
    let headers = {
        "Content-type": "application/json"
    };

    if (redirectToRouteIfUnauthenticated === true) {
        headers["Redirect-to-url-if-unauthorized"] = basePath + route;
    } else if (typeof redirectToRouteIfUnauthenticated === "string") {
        headers["Redirect-to-url-if-unauthorized"] = basePath + redirectToRouteIfUnauthenticated;
    }

    return fetch(basePath + route, {
        method: 'DELETE',
        headers: headers
    })
        .then(async response => {
            if (!response.ok) {
                await handleFail(response);
                throw new Error('Response was not "ok"');
            }
            return response.json();
        });
}
