import {handleFail} from "../general/ajax/ajax-util/fail-handler.js?v=0.4.1";
import {fetchTranslations} from "../general/ajax/fetch-translation-data.js?v=0.4.1";
import {__} from "../general/general-js/functions.js?v=0.4.1";

// Init vars
let password1Input, password2Inp;

/**
 * Add password strength check to form
 * Requirements: Password inputs MUST have the ids "password1-input" and "password2-input"
 * Warning message is placed after password input 1
 */
export function addPasswordStrengthCheck(){
    password1Input = document.getElementById('password1-input');
    password2Inp = document.getElementById('password2-input');

    // Check if passwords are the same
    password1Input.addEventListener('keyup', checkIfPasswordsMatch);
    password2Inp.addEventListener('keyup', checkIfPasswordsMatch);

    // Check if password is known to be breached
    password1Input.addEventListener('keyup', checkIfPasswordIsBreached);
}

/**
 * Check if password 1 and password 2 are identical
 */
function checkIfPasswordsMatch() {
    let submitBtn = document.querySelector('input[type="submit"]');
    if(submitBtn === null){
        submitBtn = document.querySelector('.submit-btn');
    }
    // Set button to disabled if passwords don't match or if field is empty
    submitBtn.disabled = password1Input.value !== password2Inp.value || password1Input.value === '';
}

/**
 * Check if the password has been breached
 */
function checkIfPasswordIsBreached() {
    // Create hash and make Ajax request to HIBP api and display warning if needed
    // getHash makes SHA-1 hash and returns promise
    getHash(password1Input.value)
        // makeHIBPRequest is called with as parameter the return value of getHash() promise which is the password hash
        .then(makeHIBPRequest)
        // showWarning and removeWarning are the functions executed by makeHIBPRequest promise resolve() and reject()
        .then(showWarning, removeWarning);
}

/**
 * Make request to Have I Been Pwned API
 *
 * @param {string} passwordHash
 */
function makeHIBPRequest(passwordHash) {
    return new Promise((resolve, reject) => {
        let hashPrefix = passwordHash.substring(0, 5);
        let hashSuffix = passwordHash.substring(5);

        fetch(`https://api.pwnedpasswords.com/range/${hashPrefix}`)
            .then(async response => {
                if (!response.ok) {
                    await handleFail(response);
                    reject();
                }
                return response.text();
            })
            .then(text => {
                let hashFound = text.toLowerCase().includes(hashSuffix);
                if (hashFound) {
                    resolve();
                } else {
                    reject();
                }
            })
            .catch(async error => {
                await handleFail(error);
                reject();
            });
    });
}

// Add translated warning for the user below input field
let str = __('This password is known to have been leaked and is unsafe to use');
// Fetch translations and replace str var (fetch done automatically at page loading when imported)
fetchTranslations([str]).then(response => {
    // Fill the var with a JSON of the translated words. Key is the original english words and value the translated one
    str = response[[str]] ?? str;
});
function showWarning() {
    if (null === document.getElementById('pwned-password-warning')) {
        password1Input.insertAdjacentHTML('afterend', `<span class="input-warning content-below-input"
            id="pwned-password-warning">${str}</span>`);
    }
}

/**
 * Remove warning below input field
 */
function removeWarning() {
    // If not breached, remove the warning element if it exists
    let warningElement = document.getElementById('pwned-password-warning');
    if (null !== warningElement) {
        warningElement.remove();
    }
}

/**
 * Create SHA-1 hash asynchronously
 *
 * Source: https://stackoverflow.com/a/43383990/9013718
 * @param str
 * @param algo
 * @returns {Promise<string>}
 */
function getHash(str, algo = "SHA-1") {
    let strBuf = new TextEncoder().encode(str);
    // crypto.subtle only works for HTTPS sites digest returns a promise
    return crypto.subtle.digest(algo, strBuf)
        // .then is executed only after initial promise is done (resolved) and accepts two parameters
        // first is the success callback function and second the error callback function
        // hash is the variable name of the return result of the promise before (resolved value)
        // which is passed to an anonymous function that can use this value (hashAsParam) => {/* function */}
        .then((hash) => {
            window.hash = hash;
            // Here hash is an arrayBuffer,
            // So we'll convert it to its hex version
            let result = '';
            const view = new DataView(hash);
            for (let i = 0; i < hash.byteLength; i += 4) {
                result += ('00000000' + view.getUint32(i).toString(16)).slice(-8);
            }
            return result;
        });
}