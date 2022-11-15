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

/**
 * Determine avatar path with sex
 *
 * @param {string} sex
 */
export function getAvatarPath(sex){
    switch(sex){
        case 'M':
            return "assets/client/img/avatar_male.svg";
        case 'F':
            return "assets/client/img/avatar_female.svg";
        case 'O':
        default:
            return "assets/client/img/avatar_neutral.svg";
    }
}