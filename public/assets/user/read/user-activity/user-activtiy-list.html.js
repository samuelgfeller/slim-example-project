export function getUserActivityListHtml(userActivitiesGroupedByDate) {
    let activityListHtml = '';

    for (const [dateString, userActivities] of Object.entries(userActivitiesGroupedByDate)) {
        activityListHtml += `<h3 class='collapsible-button'>${dateString}</h3>\n                
            <section class='collapsible-content'>\n`
        for (const userActivity of userActivities) {
            const {timeAndActionName, pageUrl, table, row_id, data, noteMessage,} = userActivity;

            // Build entries string
            let activityLabel = timeAndActionName;
            if (pageUrl) {
                activityLabel += ` <a href='${pageUrl}' target='_blank'>${table} ${row_id}</a>`;
            } else {
                activityLabel += ` ${table} ${row_id}`;
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

