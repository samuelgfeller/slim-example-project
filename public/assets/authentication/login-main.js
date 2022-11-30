const passwordForgottenBtn = document.getElementById('password-forgotten-btn');
const passwordInputDiv = document.getElementById('password-input-div');
const submitBtn = document.querySelector('input[type="submit"]');
const form = document.querySelector('form');
const logInBtn = document.getElementById('discrete-login-btn');

// Set max-height of passwordInputDiv to its actual height so that the value doesn't have to be hardcoded in CSS
// for the collapse animation to be smooth (if too high max height it takes some time before container shrinks)
passwordInputDiv.style.maxHeight = passwordInputDiv.scrollHeight + 'px';

/**
 * Remove password field and change form to submit password forgotten request
 */
passwordForgottenBtn.addEventListener('click', () => {
    // Remove max height on element and let CSS animation take over
    passwordInputDiv.style.maxHeight = null;
    passwordInputDiv.classList.remove('input-div-expanded');
    passwordInputDiv.classList.add('input-div-collapsed');
    submitBtn.value = 'Request password';
    form.action = 'password-forgotten';
    logInBtn.style.maxHeight = logInBtn.scrollHeight + 'px';
});

/**
 * Remove password field and change form to submit password forgotten request
 */
logInBtn.addEventListener('click', () => {
    passwordInputDiv.style.maxHeight = passwordInputDiv.scrollHeight + 'px';
    passwordInputDiv.classList.remove('input-div-collapsed');
    passwordInputDiv.classList.add('input-div-expanded');
    submitBtn.value = 'Login';
    form.action = 'login';
    // logInBtn.style.display = 'none';
    logInBtn.style.maxHeight = '0';
});


