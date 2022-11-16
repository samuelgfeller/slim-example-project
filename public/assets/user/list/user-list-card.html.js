import {getDropdownAsHtmlOptions} from "../../general/js/template/template-util.js";
import {escapeHtml} from "../../general/js/functions.js";

/**
 * HTML code for client profile card
 *
 * @return {string}
 * @param {HTMLElement} container
 * @param {Object} user userResultData object
 * @param {Object} statuses user statuses (no restriction if privileged unlike user role so passed globally)
 */
export function getUserCardHtml(container, user, statuses) {
    return `<div class="user-card" tabindex="0" data-user-id="${user.id}">
    <div class="card-content">
        <h3>${user.firstName !== null ? user.firstName : ''} ${user.surname !== null ? user.surname : ''}</h3>
        <div class="card-icon-and-span-div">
            <img src="assets/general/img/personal-data-icons/email-icon.svg" class="card-icon" alt="email">
            <span>${escapeHtml(user.email)}</span>
         </div>
            <div>
                <select name="status" class="default-select">
                ${getDropdownAsHtmlOptions(statuses, user.statusId)}
                </select>
            </div>
            <div>
                <select name="user_role_id" class="default-select">
                ${getDropdownAsHtmlOptions(user.availableUserRoles, user.userRoleId)}
                </select>
            </div>
           
    </div>
</div>`;
}

export function getUserCardLoadingPlaceholderHtml() {
    return `<div class="user-card-loading-placeholder">
        <div class="user-card-loading-placeholder-header">
            <div class="user-card-avatar-age-loading-placeholder">
                <div class="user-card-avatar-loading-placeholder">
                    <!-- Avatar-->
                    <div class="moving-loading-placeholder-part-wrapper">
                        <div class="moving-loading-placeholder-part"></div>
                    </div>
                </div>
                <!-- Age -->
                <div class="user-card-age-loading-placeholder">
                    <div class="moving-loading-placeholder-part-wrapper">
                        <div class="moving-loading-placeholder-part"></div>
                    </div>
                </div>
            </div>

        </div>
        <div class="user-card-loading-placeholder-body">
            <!-- CSS Grid -->
            <div class="user-card-name-loading-placeholder">
                <div class="moving-loading-placeholder-part-wrapper">
                    <div class="moving-loading-placeholder-part"></div>
                </div>
            </div>
            <div class="user-card-loading-placeholder-infos-container">
                <div class="user-card-location-loading-placeholder">
                    <div class="moving-loading-placeholder-part-wrapper">
                        <div class="moving-loading-placeholder-part"></div>
                    </div>
                </div>
                <div class="user-card-phone-nr-loading-placeholder">
                    <div class="moving-loading-placeholder-part-wrapper">
                        <div class="moving-loading-placeholder-part"></div>
                    </div>
                </div>
                <div class="user-card-assignee-loading-placeholder">
                    <div class="moving-loading-placeholder-part-wrapper">
                        <div class="moving-loading-placeholder-part"></div>
                    </div>
                </div>
                <div class="user-card-status-loading-placeholder">
                    <div class="moving-loading-placeholder-part-wrapper">
                        <div class="moving-loading-placeholder-part"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>`;
}
