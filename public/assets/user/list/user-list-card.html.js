import {getDropdownAsHtmlOptions} from "../../general/template/template-util.js?v=1.0.0";
import {html} from "../../general/general-js/functions.js?v=1.0.0";

/**
 * HTML code for user profile card
 *
 * @return {string}
 * @param {Object} user userResultData.php object
 * @param {Object} statuses user statuses (no restriction if privileged unlike user role so passed globally)
 */
export function getUserCardHtml(user, statuses) {
    return `<div class="user-card" tabindex="0" data-user-id="${user.id}">
    <div class="card-content">
        <h3>${user.firstName !== null ? html(user.firstName) : ''} ${user.lastName !== null ? html(user.lastName) : ''}</h3>
        <div class="card-icon-and-span-div">
            <img src="assets/general/general-img/personal-data-icons/email-icon.svg" class="card-icon default-icon" alt="email">
            <a href="mailto:${html(user.email)}">${html(user.email)}</a>
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

export function getUserCardSkeletonLoaderHtml() {
    return `<div class="user-card-skeleton-loader">
            <!-- CSS Grid -->
            <div class="user-card-name-skeleton-loader">
                <div class="moving-skeleton-loader-part-wrapper">
                    <div class="moving-skeleton-loader-part"></div>
                </div>
            </div>
            <div class="user-card-email-container">
                <div class="moving-skeleton-loader-part-wrapper">
                    <div class="moving-skeleton-loader-part"></div>
                </div>
                <div class="moving-skeleton-loader-part-wrapper">
                    <div class="moving-skeleton-loader-part"></div>
                </div>
            </div>
            <div class="user-card-dropdown-container">
                <div class="moving-skeleton-loader-part-wrapper">
                    <div class="moving-skeleton-loader-part"></div>
                </div>
                <div class="moving-skeleton-loader-part-wrapper">
                    <div class="moving-skeleton-loader-part"></div>
                </div>
            </div>
    </div>`;
}
