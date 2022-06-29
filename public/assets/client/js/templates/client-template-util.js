/**
 * Creates option list for html select
 *
 * @param {object} allEntries database key has to be object key and value is name
 * @param {number} selectedKey
 * @return {string}
 */
export function getDropdownOptions(allEntries, selectedKey){
    let optionsHtml = '';
    for (const [entryId, name] of Object.entries(allEntries)) {
        let selected = entryId === selectedKey.toString() ? `selected="selected"` : '';
        optionsHtml += `<option value="${entryId}" ${selected}>${name}</option>\n`;
    }
    return optionsHtml;
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
        case null:
            return "assets/client/img/avatar_neutral.svg";
    }
}