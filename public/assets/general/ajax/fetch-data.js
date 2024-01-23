import {basePath} from "../general-js/config.js?v=0.4.0";
import {handleFail} from "./ajax-util/fail-handler.js?v=0.4.0";

/**
 * Sends a GET request and returns result in promise
 *
 * @param {string} route only the part after base path (e.g. 'users/1'). Query params have to be added with ?param=value
 * @param {boolean|string} redirectToRouteIfUnauthenticated true or redirect route url after base path.
 * If true, the redirect url is the same as the given route
 * @return {Promise<JSON>}
 */
export function fetchData(route, redirectToRouteIfUnauthenticated = false) {
    let headers = new Headers();
    headers.append("Content-type", "application/json");

    if (redirectToRouteIfUnauthenticated === true) {
        headers.append("Redirect-to-url-if-unauthorized", basePath + route);
    } else if (typeof redirectToRouteIfUnauthenticated === "string") {
        headers.append("Redirect-to-url-if-unauthorized", basePath + redirectToRouteIfUnauthenticated);
    }

    return fetch(basePath + route, { method: 'GET', headers: headers })
        .then(async response => {
            if (!response.ok) {
                await handleFail(response);
                throw response;
            }
            return response.json();
        });
        // Without catch block as it is implemented by the function calling it
}
