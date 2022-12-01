const passwordForgottenBtn = document.getElementById('password-forgotten-btn');
const passwordInputDiv = document.getElementById('password-input-div');
const passwordInput = passwordInputDiv.querySelector('input');
const submitBtn = document.querySelector('input[type="submit"]');
const form = document.querySelector('form');
const logInBtn = document.getElementById('discrete-login-btn');

// Set max-height of passwordInputDiv to its actual height so that the value doesn't have to be hardcoded in CSS
// for the collapse animation to be smooth (if too high max height it takes some time before container shrinks)
passwordInputDiv.style.maxHeight = passwordInputDiv.scrollHeight + 'px';
// Add password input name to data attribute
passwordInput.dataset.name = passwordInput.name;

/**
 * Remove password field and change form to submit password forgotten request
 */
passwordForgottenBtn.addEventListener('click', () => {
    // Remove max height on element and let CSS animation take over
    passwordInputDiv.style.maxHeight = null;
    passwordInputDiv.classList.remove('input-div-expanded');
    passwordInputDiv.classList.add('input-div-collapsed');
    // Remove name from input to prevent it from being submitted
    passwordInput.removeAttribute('name');
    submitBtn.value = 'Request password';
    form.action = 'password-forgotten';
    logInBtn.style.maxHeight = logInBtn.scrollHeight + 'px';
});

/**
 * Add password field and change form to submit login request
 */
logInBtn.addEventListener('click', () => {
    passwordInputDiv.style.maxHeight = passwordInputDiv.scrollHeight + 'px';
    passwordInputDiv.classList.remove('input-div-collapsed');
    passwordInputDiv.classList.add('input-div-expanded');
    submitBtn.value = 'Login';
    form.action = 'login';
    // Add name to input
    passwordInput.name = passwordInput.dataset.name;
    // logInBtn.style.display = 'none';
    logInBtn.style.maxHeight = '0';
});

// If validation failed or there was a security exception the backend renders the login page. The submition url is kept
// however so when the url is password-forgotten or reset-password (when invalid token) show password forgotten form
const route = window.location.pathname.split("/").pop();
if (route === 'password-forgotten' || route === 'reset-password'){
    passwordForgottenBtn.click();
    window.history.replaceState(null, '', 'login');
}


