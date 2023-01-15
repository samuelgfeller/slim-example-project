import {loadUserList} from "./user-list-loading.js?v=0.2.0";

// Load users at page startup - this function cannot be here as it's used by the dashboard for loading in tailored wrapper
loadUserList();