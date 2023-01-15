// In square brackets to be evaluated
import {submitUpdate} from "./submit-update-data.js?v=0.2.0";
import {displayFlashMessage} from "../page-component/flash-message/flash-message.js?v=0.2.0";
import {removeSpecialChars} from "../general-js/functions.js?v=0.2.0";

/**
 *
 * @param {string} fieldName
 * @param {string} fieldValue
 * @param {string} route after base path
 * @param {boolean|string} redirectToRouteIfUnauthenticated true or redirect route url after base path.
 * If true, the redirect url is the same as the given route
 * @param {boolean} showFlashMessage
 */
export function submitFieldChangeWithFlash(
    fieldName,
    fieldValue,
    route,
    redirectToRouteIfUnauthenticated = false,
    showFlashMessage = true
) {
    submitUpdate({[fieldName]: fieldValue}, route, redirectToRouteIfUnauthenticated)
        .then(responseJson => {
            if (showFlashMessage === true){
                displayFlashMessage('success', `Successfully changed ${removeSpecialChars(fieldName)}.`);
            }
        });
}