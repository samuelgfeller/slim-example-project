export function getUserActivityListHtml(userActivitiesGroupedByDate) {
    let activityListHtml = '';

    for (const [dateString, userActivities] of Object.entries(userActivitiesGroupedByDate)) {
        activityListHtml += `<h3 class='collapsible-button'>${dateString}</h3>\n                
            <section class='collapsible-content'>\n`
        for (const userActivity of userActivities) {
            const {timeAndActionName, pageUrl, table, rowId, data} = userActivity;

            // Build entries string
            let activityLabel = timeAndActionName;
            if (pageUrl) {
                activityLabel += ` <a href='${pageUrl}' target='_blank'>${table} ${rowId}</a>`;
            } else {
                activityLabel += ` ${table} ${rowId}`;
            }
            // Generate data string
            let dataString = '';
            const userActivityData = data || {};
            for (let [column, value] of Object.entries(userActivityData)) {
                if ((value !== null || column === 'deleted_at') && !Array.isArray(value)) {
                    column = (typeof column === 'number') ? '' : `<span style='font-weight: 500'>${column}</span>:`;
                    dataString += `<br> ${column} ${(value ? value : 'null')}`;
                }
            }

            activityListHtml += `<p><b>${activityLabel}</b>${dataString}</p>`
        }
        activityListHtml += `</section>`
    }
    return activityListHtml;
}

