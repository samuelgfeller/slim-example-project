import {fetchData} from "../../../general/ajax/fetch-data.js?v=0.2.0";
import {getUserActivityListHtml} from "./user-activtiy-list.html.js?v=0.2.0";
import {initCollapsible} from "../../../general/page-component/collapsible/collapsible.js?v=0.2.0";


/**
 * Fetch and load user activities into dom
 * @param {string} queryParams `user=${userId}` or `user[]=${userId}` user id or array of user ids
 * in query string format without trailing question mark
 * @param {boolean} multipleUsers if multiple users activities are loaded
 */
export function loadUserActivities(queryParams, multipleUsers = false) {
    if (queryParams) {
        queryParams = '?' + queryParams;
    }
    const container = document.getElementById('user-activity-content');
    container.innerHTML = '';
    fetchData('users/activity' + queryParams).then(resultJson => {
        container.insertAdjacentHTML('afterbegin', getUserActivityListHtml(resultJson));
        initCollapsible();
        // Pre-open first date collapsible
        container.querySelector('.collapsible-button')?.click();
    });
}