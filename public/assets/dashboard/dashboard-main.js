import {fetchAndLoadClients} from "../client/list/client-list-loading.js?v=0.2.0";
import {fetchAndLoadClientNotes} from "../client/note/client-read-note-loading.js?v=0.2.0";
import {initFilterChipEventListeners} from "../general/page-component/filter-chip/filter-chip.js?v=0.2.0";
import {loadUserActivities} from "../user/read/user-activity/activity-main.js?v=0.2.0";
import {submitUpdate} from "../general/ajax/submit-update-data.js?v=0.2.0";

// Toggle enable / disable panel
const panelToggleButtons = document.getElementsByClassName('dashboard-panel-toggle-btn');
for (const toggleBtn of panelToggleButtons) {
    const inputCheckbox = toggleBtn.querySelector('input[type="checkbox"]');
    const panel = document.getElementById(toggleBtn.dataset.panelId);
    // Hide panels that don't have the button checked on load
    if (inputCheckbox.checked) {
        panel.style.display = null;
    } else {
        panel.style.display = 'none';
    }
    toggleBtn.addEventListener('click', (e) => {
        // Click on label triggers click event twice https://stackoverflow.com/a/19595155/9013718
        if (e.target.tagName === 'INPUT') {
            // Hide show on click
            if (inputCheckbox.checked) {
                panel.style.display = null;
            } else {
                panel.style.display = 'none';
            }
            // Send the server a list of the active panels
            const activePanelInputs = document.querySelectorAll(`.dashboard-panel-toggle-btn input[type="checkbox"]:checked`)
            let panelIds = [];
            for (const checkedInput of activePanelInputs) {
                // Get the panel id from the parent label data attribute
                panelIds.push(checkedInput.closest('label').dataset.panelId);
            }
            submitUpdate({panelIds: JSON.stringify(panelIds)}, 'dashboard-toggle-panel').then(r => {
            });
        }
    });
}

// Add toggle open - close event listeners
const panelHeaderToggleIcons = document.getElementsByClassName('toggle-panel-icon');
for (const toggleIcon of panelHeaderToggleIcons) {
    const panelHeader = toggleIcon.closest('.panel-header');
    panelHeader.addEventListener('click', () => {
        let hiddenPanels = JSON.parse(window.localStorage.getItem('hiddenPanels') ?? '{}');
        const panelContainer = toggleIcon.closest('.panel-container');
        if (!panelContainer.classList.contains('collapsed')) {
            // Collapse panel of not in class list
            panelContainer.classList.add('collapsed');
            // Add panel id to hidden panels object (as key to remove it easier later)
            hiddenPanels[panelContainer.id] = true;
        } else {
            panelContainer.classList.remove('collapsed');
            // Remove panel id from hidden panels
            delete hiddenPanels[panelContainer.id]
        }
        // Store value in localstorage
        window.localStorage.setItem('hiddenPanels', JSON.stringify(hiddenPanels));
    })
}

// Collapse panels if collapsed in localStorage
const hiddenPanelIds = JSON.parse(window.localStorage.getItem('hiddenPanels') ?? '{}');
for (const hiddenPanelId in hiddenPanelIds) {
    const hiddenPanel = document.getElementById(hiddenPanelId);
    if (hiddenPanel) {
        hiddenPanel.classList.add('collapsed');
    } else {
        // If hiddenPanel is not found it means that it doesn't exist for the user anymore so it should be deleted
        delete hiddenPanelIds[hiddenPanelId];
        // Store modified hidden panels in localstorage
        window.localStorage.setItem('hiddenPanels', JSON.stringify(hiddenPanelIds));
    }
}

// Load clients in client panels
const clientPanels = document.getElementsByClassName('client-panel');
for (const clientPanel of clientPanels) {
    let filterParams = new URLSearchParams();
    const paramsData = clientPanel.querySelectorAll('data');
    for (const paramData of paramsData) {
        filterParams.append(paramData.dataset.paramName, paramData.dataset.paramValue);
    }

    // client panels have to have a div.client-wrapper in the content section
    fetchAndLoadClients(filterParams, clientPanel.querySelector('.client-wrapper').id, false);
}

// Load notes in notes panels
const notesPanels = document.getElementsByClassName('note-panel');
for (const notePanel of notesPanels) {
    let noteFilterParam = new URLSearchParams();
    const paramsData = notePanel.querySelectorAll('data');
    for (const paramData of paramsData) {
        noteFilterParam.append(paramData.dataset.paramName, paramData.dataset.paramValue);
    }

    // client panels have to have a div.client-wrapper in the content section with a unique id
    fetchAndLoadClientNotes(noteFilterParam, notePanel.querySelector('.client-note-wrapper').id);
}

// User activity panel
const userPanel = document.getElementById('user-activity-panel');
if (userPanel) {
// Pass var to user to event handler function https://stackoverflow.com/a/45696430/9013718
    let curriedLoadUserActivityFunction = () => {
        let userActivityFilterParam = new URLSearchParams();
        const paramsData = userPanel?.querySelectorAll('.filter-chip-active span');
        for (const paramData of paramsData) {
            // Add [] to param name so that its
            userActivityFilterParam.append(paramData.dataset.paramName + '[]', paramData.dataset.paramValue);
            // Add filter id to filterIds param
            userActivityFilterParam.append('filterIds[]', paramData.dataset.filterId);
        }
        // submitUpdate(userActivityFilterParam, 'dashboard-user-activity-filter-setting').then(r => {});
        return loadUserActivities(userActivityFilterParam.toString(), true);
    }
    curriedLoadUserActivityFunction();

    initFilterChipEventListeners(curriedLoadUserActivityFunction);
}



