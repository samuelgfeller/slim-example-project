import {html} from "../general-js/functions.js?v=0.4.0";

/**
 * @param {object} allEntries database key has to be object key and value is name
 * @param {number|string} selectedKey optional selected key. If not found, no option is selected
 * @param {string|boolean} nullable or nullable value
 * @return {string}
 */
export function getDropdownAsHtmlOptions(allEntries, selectedKey = 0, nullable = false){
    let optionsHtml = '';
    if (nullable){
        // Add first option empty if nullable
        optionsHtml = `<option value="">${typeof nullable === 'string' ? nullable : ''}</option>\n`;
    }
    for (const [entryKey, name] of Object.entries(allEntries)) {
        let selected = entryKey === selectedKey?.toString() ? `selected="selected"` : '';
        optionsHtml += `<option value="${html(entryKey)}" ${selected}>${html(name)}</option>\n`;
    }
    return optionsHtml;
}

/**
 * Creates radio button entries in html
 *
 * @param {object} allEntries object key is the input value and is name the label for this radio button
 * @param {string} inputName name of the radio button input tag
 * @param {number} checkedKey optional selected key. If not found, no radio button is checked
 * @return {string}
 */
export function getRadioButtonsAsHtml(allEntries, inputName, checkedKey = 0){
    let radioButtonsHtml = '';
    for (const [key, name] of Object.entries(allEntries)) {
        let selected = key === checkedKey.toString() ? `checked="true"` : '';
        radioButtonsHtml += `<label class="form-radio-input">\n
                                <input type="radio" ${selected} name="${inputName}" value="${key}">${name}
                            </label>\n`;

    }
    return radioButtonsHtml;
}
