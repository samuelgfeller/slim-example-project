import {displayUserCreateModal} from "./user-create-modal.html.js?v=0.2.1";
import {submitModalForm} from "../../general/page-component/modal/modal-submit-request.js?v=0.2.1";
import {displayFlashMessage} from "../../general/page-component/flash-message/flash-message.js?v=0.2.1";
import {loadUserList} from "../list/user-list-loading.js?v=0.2.1";
import {fetchData} from "../../general/ajax/fetch-data.js?v=0.2.1";
import {__} from "../../general/general-js/functions.js?v=0.2.1";

let wordsToTranslate = [
    __('First name'),
    __('Last name'),
    __('E-Mail'),
    __('New password'),
    __('Repeat new password'),
    __('Status'),
    __('User role')
];
const params = new URLSearchParams();
wordsToTranslate.forEach((value) => {
    params.append('strings[]', value);
});
fetchData(`translate?${params.toString()}`).then(responseJSON => {
    console.log(responseJSON);
}).catch()

document.querySelector('#create-user-btn').addEventListener('click', displayUserCreateModal);

// Modal events need event delegation as modal is removed and added dynamically
document.addEventListener('click', e => {
// Submit request on submit button click
    if (e.target && e.target.id === 'user-create-submit-btn') {

        // Submit modal form and execute promise "then()" only if available (nothing is returned on validation error)
        submitModalForm('create-user-modal-form', 'users', 'POST')?.then((response) => {
            if (response.status === 'error') {
                displayFlashMessage('error', response.message);
            } else {
                displayFlashMessage('success', 'User created successfully.');
            }
            loadUserList();
        })
    }
});