$(document).ready(function () {

    $('#updatePasswordInp1, #updatePasswordInp2').on('keyup', function () {
        checkIfInputHaveSameVal($('#updatePasswordInp1'), $('#updatePasswordInp2'), $('#submitChangePasswordBtn'));
    });

    $('#setNewPasswordInp1, #setNewPasswordInp2').on('keyup', function () {
        checkIfInputHaveSameVal($('#setNewPasswordInp1'), $('#setNewPasswordInp2'), $('#submitSetNewPasswordBtn'));
    });
});

function checkIfInputHaveSameVal(inp1, inp2, submitBtn) {
    if (inp1.val() === inp2.val()) {
        submitBtn.attr("disabled", false);
    } else {
        submitBtn.attr("disabled", true);
    }
}