/**
 * These functions could be added to the js String properties
 * like shown in https://stackoverflow.com/a/3291856/9013718,
 * but I think @aggregate1166877 mentions an important factor to consider:
 *
 * "It has the potential to break future additions to JS. If it's code that only
 * you will use, then it's not so bad - you just update your code and move on.
 * The real issue here is when you start publishing libraries with code like this:
 * your code modifies the built-in behavior for every library using your code.
 * The consequence is that if you and another library author both override the
 * same built-ins with your own implementations, you create bugs in the other
 * library's code (or whichever is loaded last) leaving the user with debugging
 * hell of unreproducible bug reports."
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
export function escapeHtml(unsafeString) {
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
export function removeSpecialChars(string, upperCase = false){
    let outputString = string?.toString()
        .replace(/_/g, ' ')
        .replace(/-/g, ' ')
        // Remove the word "id" as this function is currently mainly used to display form elements names to users
        .replace(/id/g, ' ') // e.g. user_role_id -> user role
        // Remove extra whitespace from both ends
        .trim();
    if (upperCase === true){
        // Source: https://stackoverflow.com/a/3291856/9013718
        outputString = outputString.charAt(0).toUpperCase() + outputString.slice(1)
    }
    return outputString;
}
