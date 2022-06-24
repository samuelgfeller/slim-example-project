/**
 * HTML code for client profile card
 *
 * @param clientContainer
 * @param firstName
 * @param lastName
 * @param age
 * @param sex
 * @param location
 * @param phoneNumber
 * @param assignedUserId
 * @param statusId
 * @param allUsers
 * @param allStatuses
 * @return {string}
 */
export function getClientProfileCardHtml(clientContainer, firstName, lastName, age, sex, location, phoneNumber, assignedUserId,
                                         statusId, allUsers, allStatuses) {

    return `<div class="client-profile-card">
    <div class="profile-card-header">
        <!-- other div needed to attach bubble to img -->
        <div class="profile-card-avatar">
            <img src=${getAvatarPath(sex)} alt="avatar">
            <span class="profile-card-age">${age}</span>
        </div>
    </div>
    <div class="profile-card-content">
        <h3>${firstName} ${lastName}</h3>
        <div class="profile-card-infos-flexbox">
            <div>
                <img src="assets/client/img/location_pin_icon.svg" class="profile-card-content-icon" alt="location">
                <span>${location}</span>
            </div>
            <div>
                <img src="assets/client/img/phone.svg" class="profile-card-content-icon" alt="phone">
                <span>${phoneNumber}</span>
            </div>
        </div>
        <div class="profile-card-assignee-and-status">
            <div>
                <select name="assigned-user">
                ${getDropdownOptions(allUsers, assignedUserId)}
                </select>
            </div>
            <div>
                <select name="status">
                ${getDropdownOptions(allStatuses, statusId)}
                </select>
            </div>
        </div>
    </div>
</div>`;
}

/**
 * Creates option list for html select
 *
 * @param {object} allEntries database key has to be object key and value is name
 * @param {number} selectedKey
 * @return {string}
 */
function getDropdownOptions(allEntries, selectedKey){
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
function getAvatarPath(sex){
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