import {removeValidationErrorMessages} from "../general/ajax/ajax-util/fail-handler.js?v=0.4.0";

const passwordForgottenBtn = document.getElementById('password-forgotten-btn');
const passwordInputDiv = document.getElementById('password-input-div');
const passwordInput = passwordInputDiv.querySelector('input');
const submitBtn = document.querySelector('input[type="submit"]');
const form = document.querySelector('form');
const logInBtn = document.getElementById('discrete-login-toggle-btn');

// Set max-height of passwordInputDiv to its actual height so that the value doesn't have to be hardcoded in CSS
// for the collapse animation to be smooth (if too high max height it takes some time before the container shrinks)
passwordInputDiv.style.maxHeight = passwordInputDiv.scrollHeight + 'px';
// Add password input name to data attribute
passwordInput.dataset.name = passwordInput.name;

/**
 * Remove password field and change form to password forgotten fields
 */
passwordForgottenBtn.addEventListener('click', () => {
    changeFormToPasswordForgotten();
    removeValidationErrorMessages();
});

function changeFormToPasswordForgotten() {
    // Remove max height on element and let CSS animation take over
    passwordInputDiv.style.maxHeight = null;
    passwordInputDiv.classList.remove('input-div-expanded');
    passwordInputDiv.classList.add('input-div-collapsed');
    // Disable input to prevent it from being submitted and remove frontend validation
    passwordInput.disabled = true;
    submitBtn.value = submitBtn.dataset.requestPasswordLabel ?? 'Request password';
    form.action = 'password-forgotten';
    logInBtn.style.maxHeight = logInBtn.scrollHeight + 'px';
}

/**
 * Add password field and change form to "login" fields
 */
logInBtn.addEventListener('click', () => {
    passwordInputDiv.style.maxHeight = passwordInputDiv.scrollHeight + 'px';
    passwordInputDiv.classList.remove('input-div-collapsed');
    passwordInputDiv.classList.add('input-div-expanded');
    submitBtn.value = 'Login';
    form.action = 'login';
    passwordInput.disabled = false;
    logInBtn.style.maxHeight = '0';
    removeValidationErrorMessages();
});

// If validation failed or there was a security exception, the backend renders the login page without making
// a redirect. This means the POST submit url is kept, so if it comes back with an error (e.g. invalid token),
// it has to be changed to the login url and the password forgotten btn clicked to show the correct form fields.
const route = window.location.pathname.split("/").pop();
if (route === 'password-forgotten' || route === 'reset-password') {
    changeFormToPasswordForgotten();
    window.history.replaceState(null, '', 'login');
}


