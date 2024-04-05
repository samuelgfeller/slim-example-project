import {getAvatarPath} from "../util/client-template-util.js?v=0.4.0";
import {html} from "../../general/general-js/functions.js?v=0.4.0";
import {getDropdownAsHtmlOptions} from "../../general/template/template-util.js?v=0.4.0";

/**
 * HTML code for client profile card
 *
 * @param {object} client
 * @param allUsers
 * @param allStatuses
 * @return {string} html card
 */
export function getClientProfileCardHtml(client, allUsers, allStatuses) {
    return `<div class="client-profile-card" tabindex="0" data-client-id="${html(client.id)}">
    <div class="profile-card-header">
            <!-- other div needed to attach bubble to img -->
            <div class="profile-card-avatar">
                <img src=${getAvatarPath(client.sex)} alt="avatar">
        ${// Display age if content not empty
        client.age !== null && client.age !== '' ? `<span class="profile-card-age">${html(client.age)}</span>` : ''
    }
            </div>
        </div>
        <div class="profile-card-content">
            <h3 data-deleted="${client.deletedAt !== null ? '1' : '0'}"
            >${client.firstName !== null ? html(client.firstName) : ''} ${
        client.lastName !== null ? html(client.lastName) : ''}</h3>
            <div class="profile-card-infos-flexbox">
        ${// Display location icon and content if not empty 
        client.location !== null && client.location !== '' ?
            `<div>
                     <img src="assets/general/general-img/personal-data-icons/location-icon.svg" 
                            class="profile-card-content-icon default-icon" alt="location">
                     <span>${html(client.location)}</span>
                 </div>` : ''
    }
        ${// Display location icon and content if not empty 
        client.phone !== null && client.phone !== '' ?
            `<div>
                     <img src="assets/general/general-img/personal-data-icons/phone-icon.svg" 
                            class="profile-card-content-icon default-icon" alt="phone">
                     <span>${html(client.phone)}</span>
                 </div>` : ''
    }
            </div>
            <div class="profile-card-assignee-and-status">
                <div>
                    <select name="user_id" class="default-select" 
                            ${client.assignedUserPrivilege.includes('U') ? '' : 'disabled'}>
                        ${getDropdownAsHtmlOptions(allUsers, client.userId, ' ')}
                    </select>
                </div>
                <div>
                    <select name="client_status_id" class="default-select"
                            ${client.clientStatusPrivilege.includes('U') ? '' : 'disabled'}>
                        ${getDropdownAsHtmlOptions(allStatuses, client.clientStatusId, true)}
                    </select>
                </div>
            </div>
        </div>
</div>`;
}

export function getClientProfileCardLoadingPlaceholderHtml() {
    return `<div class="client-profile-card-skeleton-loader">
        <div class="client-profile-card-skeleton-loader-header">
            <div class="client-profile-card-avatar-age-skeleton-loader">
                <div class="client-profile-card-avatar-skeleton-loader">
                    <!-- Avatar-->
                    <div class="moving-skeleton-loader-part-wrapper">
                        <div class="moving-skeleton-loader-part"></div>
                    </div>
                </div>
                <!-- Age -->
                <div class="client-profile-card-age-skeleton-loader">
                    <div class="moving-skeleton-loader-part-wrapper">
                        <div class="moving-skeleton-loader-part"></div>
                    </div>
                </div>
            </div>

        </div>
        <div class="client-profile-card-skeleton-loader-body">
            <!-- CSS Grid -->
            <div class="client-profile-card-name-skeleton-loader">
                <div class="moving-skeleton-loader-part-wrapper">
                    <div class="moving-skeleton-loader-part"></div>
                </div>
            </div>
            <div class="client-profile-card-skeleton-loader-infos-container">
                <div class="client-profile-card-location-skeleton-loader">
                    <div class="moving-skeleton-loader-part-wrapper">
                        <div class="moving-skeleton-loader-part"></div>
                    </div>
                </div>
                <div class="client-profile-card-phone-nr-skeleton-loader">
                    <div class="moving-skeleton-loader-part-wrapper">
                        <div class="moving-skeleton-loader-part"></div>
                    </div>
                </div>
                <div class="client-profile-card-assignee-skeleton-loader">
                    <div class="moving-skeleton-loader-part-wrapper">
                        <div class="moving-skeleton-loader-part"></div>
                    </div>
                </div>
                <div class="client-profile-card-status-skeleton-loader">
                    <div class="moving-skeleton-loader-part-wrapper">
                        <div class="moving-skeleton-loader-part"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>`;
}
