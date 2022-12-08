import {fetchAndLoadClients, fetchAndLoadClientsEventHandler} from "./client-list-loading.js?v=0.1";

// Load clients at page startup
fetchAndLoadClients();

initFilterChipEventListeners(fetchAndLoadClientsEventHandler);

// Filter
document.getElementById('name-search-input').addEventListener('input', fetchAndLoadClientsEventHandler);