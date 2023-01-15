import {getDropdownAsHtmlOptions} from "../../general/template/template-util.js?v=0.2.0";
import {escapeHtml} from "../../general/general-js/functions.js?v=0.2.0";

/**
 * HTML code for client profile card
 *
 * @return {string}
 * @param {Object} user userResultData.php object
 * @param {Object} statuses user statuses (no restriction if privileged unlike user role so passed globally)
 */
export function getUserCardHtml(user, statuses) {
    return `<div class="user-card" tabindex="0" data-user-id="${user.id}">
    <div class="card-content">
        <h3>${user.firstName !== null ? user.firstName : ''} ${user.surname !== null ? user.surname : ''}</h3>
        <div class="card-icon-and-span-div">
            <img src="assets/general/general-img/personal-data-icons/email-icon.svg" class="card-icon" alt="email">
            <a href="mailto:${escapeHtml(user.email)}">${escapeHtml(user.email)}</a>
        </div>
        <div class="user-card-dropdown-flexbox">
            <select name="status" class="default-select" 
                    ${user.statusPrivilege.includes('U') ? '' : 'disabled'}>
                ${getDropdownAsHtmlOptions(statuses, user.status)}
            </select>
            <select name="user_role_id" class="default-select" 
                    ${user.userRolePrivilege.includes('U') ? '' : 'disabled'}>
                ${getDropdownAsHtmlOptions(user.availableUserRoles, user.userRoleId)}
            </select>
        </div>
           
    </div>
</div>`;
}

export function getUserCardLoadingPlaceholderHtml() {
    return `<div class="user-card-loading-placeholder">
            <!-- CSS Grid -->
            <div class="user-card-name-loading-placeholder">
                <div class="moving-loading-placeholder-part-wrapper">
                    <div class="moving-loading-placeholder-part"></div>
                </div>
            </div>
            <div class="user-card-email-container">
                <div class="moving-loading-placeholder-part-wrapper">
                    <div class="moving-loading-placeholder-part"></div>
                </div>
                <div class="moving-loading-placeholder-part-wrapper">
                    <div class="moving-loading-placeholder-part"></div>
                </div>
            </div>
            <div class="user-card-dropdown-container">
                <div class="moving-loading-placeholder-part-wrapper">
                    <div class="moving-loading-placeholder-part"></div>
                </div>
                <div class="moving-loading-placeholder-part-wrapper">
                    <div class="moving-loading-placeholder-part"></div>
                </div>
            </div>
    </div>`;
}
