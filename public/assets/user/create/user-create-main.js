import {displayUserCreateModal} from "./user-create-modal.html.js?v=4.0.2";
import {displayFlashMessage} from "../../general/page-component/flash-message/flash-message.js?v=4.0.2";
import {loadUserList} from "../list/user-list-loading.js?v=4.0.2";
import {__} from "../../general/general-js/functions.js?v=4.0.2";
import {submitModalForm} from "../../general/ajax/modal-submit-request.js?v=4.0.2";


document.querySelector('#create-user-btn').addEventListener('click', displayUserCreateModal);
// Modal events need event delegation as modal is removed and added dynamically
document.addEventListener('click', e => {
// Submit request on submit button click
    if (e.target && e.target.id === 'user-create-submit-btn') {

        // Submit modal form and execute promise "then()" only if available (nothing is returned on validation error)
        submitModalForm('create-user-modal-form', 'users', 'POST')
            ?.then((responseJson) => {
                if (responseJson.status === 'error') {
                    displayFlashMessage('error', responseJson.message);
                } else {
                    displayFlashMessage('success', __('User created successfully'));
                }
                loadUserList();
            })
            ?.catch(error => {
                console.error(error);
            })
    }
});