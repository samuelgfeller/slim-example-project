import {fetchData} from "../../../general/ajax/fetch-data.js?v=0.4.0";
import {getUserActivityListHtml} from "./user-activtiy-list.html.js?v=0.4.0";
import {initCollapsible} from "../../../general/page-component/collapsible/collapsible.js?v=0.4.0";


/**
 * Fetch and load user activity list into dom
 *
 * @param {string} queryParams `user=${userId}` or `user[]=${userId}` user id or array of user ids
 * in query string format without trailing question mark
 */
export function loadUserActivities(queryParams) {
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