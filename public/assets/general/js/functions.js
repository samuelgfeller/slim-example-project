/**
 * For data retrieved with Ajax the html escape has to be done in the frontend.
 * https://stackoverflow.com/questions/6366849/html-escaping-the-data-returned-from-ajax-json
 *
 * Source: https://stackoverflow.com/questions/6234773/can-i-escape-html-special-chars-in-javascript
 *
 * @param {string} unsafe
 * @return {string}
 */
export function escapeHtml(unsafe) {
    // Number has not the function replace() hence toString()
    return unsafe.toString()
        .replace(/&/g, "&amp;")
        .replace(/</g, "&lt;")
        .replace(/>/g, "&gt;")
        .replace(/"/g, "&quot;")
        .replace(/'/g, "&#039;");
}
