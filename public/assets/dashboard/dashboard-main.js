import {fetchAndLoadClients} from "../client/list/client-list-loading.js?v=0.1";

const clientPanels = document.getElementsByClassName('client-panel');
for (const clientPanel of clientPanels) {
    let filterParams = [];
    const paramsData = clientPanel.querySelectorAll('data');
    for (const paramData of paramsData) {
        filterParams.push({paramName: paramData.dataset.paramName, paramValue: paramData.dataset.paramValue});
    }
    console.log(filterParams, clientPanel.querySelector('.client-wrapper').id);
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