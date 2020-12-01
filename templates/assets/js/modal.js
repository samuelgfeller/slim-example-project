$(document).ready(function () {
    $('#pageContent').on('click', '.closeModal', function () {
        $('.modal').remove();
    });
    $('#pageContent').on('click', '.modal', function (e) {
        if ($('.modal').text() == e.target.textContent) {
            $('.modal').remove();
        }
    });
});

/**
 * Create and show moadal with given content
 *
 * @param header
 * @param body
 * @param footer
 * @param appendTo jquery selector for e.g. $('#divId')
 */
function createModal(header, body, footer, appendTo){
    // Id not important because there can only be one modal at a time
    $('<div class="modal">' +
        '<div class="modal-content">' +
        '<div class="modal-header">' +
        '<span class="closeModal">&times;</span>' +
        header +
        '</div>' +
        '<div class="modal-body">' +
        body +
        '</div>' +
        '<div class="modal-footer">' +
        footer +
        '</div>' +
        '</div>' +
        '</div>').appendTo(appendTo);
    $('.modal').show();
}

function closeModal() {
    $('.modal').remove();
}

/*
x
// Get the modal
var modal = document.getElementById("myModal");

// Get the button that opens the modal
var btn = document.getElementById("myBtn");

// Get the <span> element that closes the modal
var span = document.getElementsByClassName("close")[0];
// When the user clicks the button, open the modal
btn.onclick = function() {
    modal.style.display = "block";
}

// When the user clicks on <span> (x), close the modal
span.onclick = function() {
    modal.style.display = "none";
}

// When the user clicks anywhere outside of the modal, close it
window.onclick = function(event) {
    if (event.target == modal) {
        modal.style.display = "none";
    }
}*/
