/**
 * These functions could be added to the js String properties
 * like shown in https://stackoverflow.com/a/3291856/9013718,
 * but I think @aggregate1166877 mentions an important factor to consider
 * which is that it could break other code.
 */


/**
 * For data retrieved with Ajax the html escape has to be done in the frontend.
 * https://stackoverflow.com/questions/6366849/html-escaping-the-data-returned-from-ajax-json
 *
 * Source: https://stackoverflow.com/questions/6234773/can-i-escape-html-special-chars-in-javascript
 *
 * @param {string|number} unsafeString
 * @return {string}
 */
export function html(unsafeString) {
    // Number has not the function replace() hence toString()
    return unsafeString?.toString()
        .replace(/&/g, "&amp;")
        .replace(/</g, "&lt;")
        .replace(/>/g, "&gt;")
        .replace(/"/g, "&quot;")
        .replace(/'/g, "&#039;");
}

/**
 * Remove special chars, the word "id" and adds uppercase to first letter
 * // e.g. user_role_id -> user role || User role
 *
 * @param {string} string
 * @param {boolean} upperCase
 * @return {string} string without special chars: -_ and first letter upper case if requested
 */
export function removeSpecialChars(string, upperCase = false) {
    let outputString = string?.toString()
        .replace(/_/g, ' ')
        .replace(/-/g, ' ')
        // Remove the word "id" as this function is currently mainly used to display form elements names to users
        .replace(/id/g, ' ') // e.g. user_role_id -> user role
        // Remove extra whitespace from both ends
        .trim();
    if (upperCase === true) {
        // Source: https://stackoverflow.com/a/3291856/9013718
        outputString = outputString.charAt(0).toUpperCase() + outputString.slice(1)
    }
    return outputString;
}

/**
 * Empty function returning the argument value.
 * Function used so that Poedit knows that a string has to be translated.
 *
 * @param {string} string
 * @returns {string}
 */
export function __(string) {
    return string;
}
