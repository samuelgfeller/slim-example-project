import {createModal} from "../../general/page-component/modal/modal.js?v=4.0.2";
import {addPasswordStrengthCheck} from "../../authentication/password-strength-checker.js?v=4.0.2";
import {__} from "../../general/general-js/functions.js?v=4.0.2";

/**
 * Create and display modal box to change password
 */
export function displayChangePasswordModal() {
    // If the old password should be asked or not
    let oldPasswordRequested = document.getElementById('change-password-btn').dataset.oldPasswordRequested;

    // Construct modal
    let header = `<h2>${__('Change password')}</h2>`;
    let body = `<div>
<form action="javascript:void(0);" class="one-row-modal-form" id="change-password-modal-form">
    ${// Ask for old password if requested
        oldPasswordRequested !== 'false' ?
            `<div class="form-input-div">
                    <label for="old-password-inp">${__('Old password')}</label>
                    <input type="password" name="old_password" id="old-password-inp" minlength="3" required 
                    class="form-input" autocomplete="current-password">
                </div>` : ''
    }
    <div class="form-input-div" id="password1-input-div">
    <label for="password1-input">${__('New password')}</label>
    <input type="password" name="password" id="password1-input" minlength="3" required class="form-input" 
    autocomplete="new-password"">
    </div>
    <div class="form-input-div">
    <label for="password2-input">${__('Repeat new password')}</label>
    <input type="password" name="password2" id="password2-input" minlength="3" required 
    autocomplete="new-password" class="form-input">
    </div>
    </div>`;
    let footer = `<input type="submit" id="change-password-submit-btn" class="submit-btn modal-submit-btn" 
value="${__('Change password')}">
    <form>
    <div class="clearfix">
    </div>`
    ;
    document.querySelector('body').insertAdjacentHTML('afterbegin', '<div id="modal-form"></div>');
    let container = document.getElementById('modal-form');
    createModal(header, body, footer, container);
    addPasswordStrengthCheck();
}
