import {basePath} from "../../general/js/config.js";

let userTableRows = document.querySelectorAll('tbody tr');
for (let tr of userTableRows){
    tr.addEventListener('click', () => {
        window.location.href = basePath + 'users/' + tr.dataset.userId;
    });
}
