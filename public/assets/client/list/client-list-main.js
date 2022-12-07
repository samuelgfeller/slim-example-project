import {fetchAndLoadClients} from "./client-list-loading.js?v=0.1";

// Load clients at page startup
fetchAndLoadClients();

initFilterChipEventListeners(fetchAndLoadClients);

// Filter
document.getElementById('name-search-input').addEventListener('input', fetchAndLoadClients);