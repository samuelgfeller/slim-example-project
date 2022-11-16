/**
 * Creates option list for html select
 *
 * @param {object} allEntries database key has to be object key and value is name
 * @param {number|string} selectedKey optional selected key. If not found, no option is selected
 * @return {string}
 */
import {escapeHtml} from "../functions.js";

export function getDropdownAsHtmlOptions(allEntries, selectedKey = 0){
    let optionsHtml = '';
    for (const [entryKey, name] of Object.entries(allEntries)) {
        let selected = entryKey === selectedKey?.toString() ? `selected="selected"` : '';
        optionsHtml += `<option value="${entryKey}" ${selected}>${escapeHtml(name)}</option>\n`;
    }
    return optionsHtml;
}