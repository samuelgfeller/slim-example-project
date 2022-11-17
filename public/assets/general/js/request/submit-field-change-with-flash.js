// In square brackets to be evaluated
import {submitUpdate} from "./submit-update-data.js";
import {displayFlashMessage} from "../requestUtil/flash-message.js";
import {removeSpecialChars} from "../functions.js";

/**
 *
 * @param {string} fieldName
 * @param {string} fieldValue
 * @param {string} route after base path
 * @param {string|null} redirectUrlIfUnauthenticated
 * @param {boolean} showFlashMessage
 */
export function submitFieldChangeWithFlash(
    fieldName,
    fieldValue,
    route,
    redirectUrlIfUnauthenticated = null,
    showFlashMessage = true
) {
    submitUpdate({[fieldName]: fieldValue}, route, redirectUrlIfUnauthenticated)
        .then(responseJson => {
            if (showFlashMessage === true){
                displayFlashMessage('success', `Successfully changed ${removeSpecialChars(fieldName)}.`);
            }
        });
}