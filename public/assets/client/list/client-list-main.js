import {fetchAndLoadClients, fetchAndLoadClientsEventHandler} from "./client-list-loading.js?v=0.4.2";
import {initFilterChipEventListeners} from "../../general/page-component/filter-chip/filter-chip.js?v=0.4.2";

// Load clients at page startup
fetchAndLoadClients();

initFilterChipEventListeners(fetchAndLoadClientsEventHandler);

// Filter
document.getElementById('name-search-input').addEventListener('input', fetchAndLoadClientsEventHandler);