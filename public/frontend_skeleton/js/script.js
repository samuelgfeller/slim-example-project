$(document).ready(function () {

    let searchParams = new URLSearchParams(window.location.search);
    if(searchParams.has('wrongpass') && searchParams.get('wrongpass') === '1') {
        let passDiv = document.getElementById('updatePasswordDiv');
        passDiv.classList.add('wrongCredInput');
        passDiv.style.display="block";
        passDiv.insertAdjacentHTML('afterbegin',"<span class=\"errSpan\">Wrong password. Please try again.</span><br><br>");

    }

    $('#updatePasswordInp1, #updatePasswordInp2').on('keyup', function () {
        checkIfInputHaveSameVal($('#updatePasswordInp1'),$('#updatePasswordInp2'),$('#submitChangePasswordBtn'));
    });

    $('#setNewPasswordInp1, #setNewPasswordInp2').on('keyup', function () {
        checkIfInputHaveSameVal($('#setNewPasswordInp1'),$('#setNewPasswordInp2'),$('#submitSetNewPasswordBtn'));
    });

    $('#changeEmailBtn').on('click', function () {
        $('#changeEmailDiv').toggle();
    });

    $('#updPasswordBtn').on('click', function () {
        $('#updatePasswordDiv').toggle();
    });


    $('#logoutBtn').on('click', function () {
        window.location.replace('/logout');
    });

    $('#delAccBtn').on('click', function () {
        if(confirm('Delete account? (If you wish to undo this action, contact me))')){
            window.location.replace('/delete_account');
        }
    });

    $('.delIcon').on('click',function (){
        let post_id = $(this).data('id');
        if(confirm('Are you sure that you want to delete this post?')){
            $.ajax({
                url: 'post/delete',
                type: 'post',
                data: {
                    'post_id': post_id,
                }
            }).done(function (output) {
                if (output === 'success') {
                    $('#post'+post_id).hide();
                } else {
                    console.log(output);
                    alert('Couldn\'t delete but reload the page to be sure.' +
                        'If its gone then it got deleted otherwise try again and or contact me');
                }
            }).fail(function (output) {
                console.log(output);
                alert('Error while deleting');
            });
        }
    });
});

function checkIfInputHaveSameVal(inp1, inp2, submitBtn) {
    if (inp1.val() === inp2.val()) {
        submitBtn.attr("disabled", false);
    }else{
        submitBtn.attr("disabled", true);
    }
}
