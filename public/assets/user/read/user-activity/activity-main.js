import {fetchData} from "../../../general/js/request/fetch-data.js?v=0.1";
import {getUserActivityListHtml} from "./user-activtiy-list.html.js?v=0.1";
import {initCollapsible} from "../../../general/js/pageComponents/collapsible.js?v=0.1";

export function loadUserActivities(userId, userActivityWrapperId = 'user-activity-content') {
    fetchData('users/activity/' + userId).then(resultJson => {
        document.getElementById(userActivityWrapperId).insertAdjacentHTML(
            'afterbegin', getUserActivityListHtml(resultJson)
        );
        initCollapsible();
    })
}