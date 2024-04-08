/**
 * Return form element in object where key is the element name
 * and value the element value.
 * Source https://gomakethings.com/how-to-serialize-form-data-with-vanilla-js/
 *
 * @param form Form element
 * @return {{}}
 */
export function getFormData(form) {
    let formObject = {};
    let formData = new FormData(form);
    for (let [key, value] of formData) {
        if (value === '') {
            value = null;
        }
        formObject[key] = value;
    }
    return formObject;
}

/**
 * After saving, the user should know that something is happening
 * and to indicate that the form is disabled
 *
 * @param formId
 */
export function toggleEnableDisableForm(formId) {
    formId = '#' + formId;
    // Select all inputs, textareas and select fields from form
    let formElements = document.querySelectorAll(formId + ' input, ' + formId + ' textarea,' + formId + ' select');
    // Opposite of disabled state of first form element
    let disabledState = !formElements[0].disabled;
    for (let formElement of formElements) {
        formElement.disabled = disabledState;
    }
}