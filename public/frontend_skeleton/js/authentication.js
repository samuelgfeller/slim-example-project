$(document).ready(function () {

    /*$('#updatePasswordInp1, #updatePasswordInp2').on('keyup', function () {
        checkIfInputHaveSameVal($('#updatePasswordInp1'), $('#updatePasswordInp2'), $('#submitChangePasswordBtn'));
    });*/

    $('#registerPassword1Inp, #registerPassword2Inp').on('keyup', function () {
        checkIfInputHaveSameVal($('#registerPassword1Inp'), $('#registerPassword2Inp'), $('#registerSubmitBtn'));
    });
});


function checkIfInputHaveSameVal(inp1, inp2, submitBtn) {
    if (inp1.val() === inp2.val() && inp1.val() !== "") {
        submitBtn.attr("disabled", false);
    } else {
        submitBtn.attr("disabled", true);
    }
}

