import {getAvatarPath, getDropdownOptions} from "./client-template-util.js";

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

export function getClientProfileCardLoadingPlaceholderHtml(){
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
