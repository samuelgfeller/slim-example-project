import {fetchAndLoadClients, fetchAndLoadClientsEventHandler} from "./client-list-loading.js?v=1.0.0";
import {initFilterChipEventListeners} from "../../general/page-component/filter-chip/filter-chip.js?v=1.0.0";

// Load clients at page startup
fetchAndLoadClients();

initFilterChipEventListeners(fetchAndLoadClientsEventHandler);

// Filter
document.getElementById('name-search-input').addEventListener('input', fetchAndLoadClientsEventHandler);