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
        // Overwriting general error message to forbidden
        errorMsg = 'Forbidden. You do not have access to this area or function';
    }

    if (xhr.status === 404 || xhr.status === '403'){
        // Overwriting general error message to forbidden
        errorMsg = 'Page not found!';
        window.open('https://www.amazon.de/asdf');
    }


    if (xhr.status === 500 || xhr.status === '500'){
        // Overwriting general error message to server error
        errorMsg = 'Internal server error';
    }


    // todo add more status verifications

    // Add error messages if they are given by the backend
    if(typeof xhr.responseJSON.message !== 'undefined' ){
        // If we know the error message we can add it to the error popup
        errorMsg += '\nMessage: '+xhr.responseJSON.message;
    }

    errorMsg += '\nCode: '+ xhr.status;
    // Output error to user
    alert(errorMsg);
}