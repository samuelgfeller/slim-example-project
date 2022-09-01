import {initActivityTextareasEventListeners} from "./text-area-event-listener-setup.js";


const clientStatus = document.querySelector('select[name="client_status"]');

clientStatus.addEventListener('change', function (e) {
    // Put selected option into select data attribute
    this.dataset.color = this.value;
    switch (this.innerText) {
        // case ''
    }
});

window.addEventListener("DOMContentLoaded", function (event) {
    initActivityTextareasEventListeners();
});





