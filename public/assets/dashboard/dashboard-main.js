import {fetchAndLoadClients} from "../client/list/client-list-loading.js?v=0.1";

// Add toggle open - close event listeners
const toggleIcons = document.getElementsByClassName('toggle-panel-icon');
for (const toggleIcon of toggleIcons) {
    const panelHeader = toggleIcon.closest('.panel-header');
    const panelContent = toggleIcon.closest('.panel-container').querySelector('.panel-content');
    panelHeader.addEventListener('click', () => {
        toggleIcon.classList.toggle('active');
        if (panelContent.style.maxHeight) {
            // Reset values to default to open panel
            panelContent.style.maxHeight = null;
            panelContent.style.padding = null;
            panelHeader.style.borderRadius = null;
        } else {
            // Collapse panel
            panelContent.style.maxHeight = '0';
            // Remove padding that was inside so that its fully collapsed
            panelContent.style.padding = '0';
            // Make all borders rounded when collapsed
            panelHeader.style.borderRadius = '32px';
        }
    })
}

const clientPanels = document.getElementsByClassName('client-panel');
for (const clientPanel of clientPanels) {
    let filterParams = [];
    const paramsData = clientPanel.querySelectorAll('data');
    for (const paramData of paramsData) {
        filterParams.push({paramName: paramData.dataset.paramName, paramValue: paramData.dataset.paramValue});
    }
    fetchAndLoadClients(filterParams, clientPanel.querySelector('.client-wrapper').id);
}


// const toggleShrinkButtons = document.getElementsByClassName('client-panel');
// for (const toggleShrinkBtn of toggleShrinkButtons) {
//     toggleShrinkBtn.addEventListener('click', () => {
//         const container = toggleShrinkBtn.closest('.panel-container');
//         if (container.style.maxWidth) {
//             container.style.maxWidth = null;
//         } else {
//             container.style.maxWidth = '45%';
//         }
//     })
// }