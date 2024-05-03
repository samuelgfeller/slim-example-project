import {loadUserList} from "./user-list-loading.js?v=0.4.1";

// Load users at page startup - this function cannot be in this file as it's used by the dashboard for loading in tailored wrapper
loadUserList();