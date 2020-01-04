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
    let errorMsg = 'Request failed';

    if (xhr.status === 401 || xhr.status === '401'){
        // Overwriting general error message to unauthorized
        errorMsg = 'Permission denied';
    }
    // todo add more status verifications

    // Add error messages if they are given by the backend
    if(typeof xhr.responseJSON.message !== 'undefined' ){
        // If we know the error message we can add it to the error popup
        errorMsg += '\nMessage: '+xhr.responseJSON.message;
    }
    alert(errorMsg);
}