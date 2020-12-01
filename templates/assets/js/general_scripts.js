$(document).ready(function () {

});


function showLoader(containerId) {
    let html = '<span></span> ' +
        '<span></span> ' +
        '<span></span> ' +
        '<span></span> ' +
        '<span></span> ' +
        '<span></span> ' +
        '<span></span> ' +
        '<span></span> ';
    $('#'+containerId).append(html);
}

function hideLoader(containerId){
    $('#'+containerId).empty();
}

/**
 * If a request fails this function can be called which gives the user
 * information about which error it is
 *
 * @param xhr
 */
function handleFail(xhr){
    let errorMsg = 'Request failed. Please try again';

    if (xhr.status === 401 || xhr.status === '401'){
        // Overwriting general error message to unauthorized
        errorMsg = 'Access denied please authenticate and try again';
    }

    if (xhr.status === 403 || xhr.status === '403'){
        errorMsg = 'Forbidden. You do not have access to this area or function';
    }

    if (xhr.status === 404 || xhr.status === '404'){
        errorMsg = 'Page not found!';
    }

    if (xhr.status === 500 || xhr.status === '500'){
        errorMsg = 'Internal server error';
    }

    // Add error messages if they are given by the backend
    if(typeof xhr.responseJSON.message !== 'undefined' ){
        // If we know the error message we can add it to the error popup
        errorMsg += '\nMessage: '+xhr.responseJSON.message;
    }

    errorMsg += '\nCode: '+ xhr.status;
    // Output error to user
    alert(errorMsg);
}

/**
 * Check html validity of form and display browser default error
 *
 * Source: https://stackoverflow.com/a/11867013/9013718
 *
 * @param formId
 */
function formIsValid(formId){
    if(!document.getElementById(formId).checkValidity()) {
        // If the form is invalid, submit it. The form won't actually submit;
        // this will just cause the browser to display the native HTML5 error messages.
        $('<input type="submit">').hide().appendTo($('#'+formId)).click().remove();
        return false;
    }
    return true;
}