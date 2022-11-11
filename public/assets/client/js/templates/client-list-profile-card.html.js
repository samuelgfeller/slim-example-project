import {getAvatarPath, getDropdownAsHtmlOptions} from "./client-template-util.js";
import {escapeHtml} from "../../../general/js/functions.js";

/**
 * HTML code for client profile card
 *
 * @param clientContainer
 * @param clientId
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
export function getClientProfileCardHtml(clientContainer, clientId, firstName, lastName, age, sex, location, phoneNumber, assignedUserId,
                                         statusId, allUsers, allStatuses) {

    return `<div class="client-profile-card" tabindex="0" data-client-id="${clientId}">
    <div class="profile-card-header">
        <!-- other div needed to attach bubble to img -->
        <div class="profile-card-avatar">
            <img src=${getAvatarPath(sex)} alt="avatar">
    ${(() => { // Only display age if content not empty
        if (age !== null && age !== '') {
            return `<span class="profile-card-age">${escapeHtml(age)}</span>`
        }
        return '';
    })()}
        </div>
    </div>
    <div class="profile-card-content">
        <h3>${firstName !== null ? firstName : ''} ${lastName !== null ? lastName : ''}</h3>
        <div class="profile-card-infos-flexbox">
    ${(() => { // Only display location icon and content if not empty 
        if (location !== null && location !== '') {
            return `<div>
                        <img src="assets/client/img/location_pin_icon.svg" class="profile-card-content-icon" alt="location">
                        <span>${escapeHtml(location)}</span>
                    </div>`
        }
        return '';
    })()}
    ${(() => { // Only display location icon and content if not empty 
        if (phoneNumber !== null && phoneNumber !== '') {
            return `<div>
                        <img src="assets/client/img/phone.svg" class="profile-card-content-icon" alt="phone">
                        <span>${escapeHtml(phoneNumber)}</span>
                    </div>`
        }
        return '';
    })()}
        </div>
        <div class="profile-card-assignee-and-status">
            <div>
                <select name="assigned-user" class="default-select">
                ${getDropdownAsHtmlOptions(allUsers, assignedUserId)}
                </select>
            </div>
            <div>
                <select name="status" class="default-select">
                ${getDropdownAsHtmlOptions(allStatuses, statusId)}
                </select>
            </div>
        </div>
    </div>
</div>`;
}

export function getClientProfileCardLoadingPlaceholderHtml() {
    return `<div class="client-profile-card-loading-placeholder">
        <div class="client-profile-card-loading-placeholder-header">
            <div class="client-profile-card-avatar-age-loading-placeholder">
                <div class="client-profile-card-avatar-loading-placeholder">
                    <!-- Avatar-->
                    <div class="moving-loading-placeholder-part-wrapper">
                        <div class="moving-loading-placeholder-part"></div>
                    </div>
                </div>
                <!-- Age -->
                <div class="client-profile-card-age-loading-placeholder">
                    <div class="moving-loading-placeholder-part-wrapper">
                        <div class="moving-loading-placeholder-part"></div>
                    </div>
                </div>
            </div>

        </div>
        <div class="client-profile-card-loading-placeholder-body">
            <!-- CSS Grid -->
            <div class="client-profile-card-name-loading-placeholder">
                <div class="moving-loading-placeholder-part-wrapper">
                    <div class="moving-loading-placeholder-part"></div>
                </div>
            </div>
            <div class="client-profile-card-loading-placeholder-infos-container">
                <div class="client-profile-card-location-loading-placeholder">
                    <div class="moving-loading-placeholder-part-wrapper">
                        <div class="moving-loading-placeholder-part"></div>
                    </div>
                </div>
                <div class="client-profile-card-phone-nr-loading-placeholder">
                    <div class="moving-loading-placeholder-part-wrapper">
                        <div class="moving-loading-placeholder-part"></div>
                    </div>
                </div>
                <div class="client-profile-card-assignee-loading-placeholder">
                    <div class="moving-loading-placeholder-part-wrapper">
                        <div class="moving-loading-placeholder-part"></div>
                    </div>
                </div>
                <div class="client-profile-card-status-loading-placeholder">
                    <div class="moving-loading-placeholder-part-wrapper">
                        <div class="moving-loading-placeholder-part"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>`;
}
