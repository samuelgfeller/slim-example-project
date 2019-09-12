$(document).ready(function () {
    //Load users
    $.ajax({
        url: 'users',
        type: 'get',
        /*        data: {
                    'place_id': placeId,
                }*/
    }).done(function (output) {
        console.log(output);
        // output = JSON.parse(output);
        if (output !== '') {
            if (output === '[]') {
            } else {
                let users = output;
                // let places = JSON.parse(output);
                $('.usersDiv').empty();
                $.each(users, function (index, value) {
                    $('<div class="singleBox" id="user' + value.id + '">' +
                        '<div class="boxContent">' +
                        '<div class="boxInnerContent">' +
                        '<img src="frontend_skeleton/img/edit_icon.svg" class="editIcon cursorPointer" data-id="' + value.id + '" alt="edit">' +
                        '<img src="frontend_skeleton/img/del_icon.svg" class="delIcon cursorPointer" data-id="' + value.id + '" alt="del">' +
                        '<h3 class="boxHeader">' + value.name + '</h3>' +
                        '<p><span class="infoInBoxSpan">Email: </span><b>' + value.email + '</b></p>' +
                        '<p><span class="infoInBoxSpan">Updated at: </span><b>' + value.updated_at + '</b></p>' +
                        '<p><span class="infoInBoxSpan">Created at: </span>' + value.created_at + '</p>' +
                        '</div>' +
                        '</div>' +
                        '</div>').appendTo($('.usersDiv'));
                });
            }
        } else {
            console.log(output);
        }
    }).fail(function (output) {
        alert('Error while fetching data');
    });

    $('.usersDiv').on('click', '.editIcon', function () {
        let id = $(this).data('id');
        $('<div class="modal" id="myModal">' +
            '<div class="modal-content">' +
            '<div class="modal-header">' +
            '<span class="closeModal">&times;</span>' +
            '<h2>Edit user</h2>' +
            '</div>' +
            '<form action="users/' + id + '" class="blueForm modalForm" autocomplete="on">' +
            '<div class="modal-body">' +
            '<b><label for="updateNameInp"">Name: </label></b>' +
            '<input type="text" name="name" id="updateNameInp" value="" placeholder="loading..." maxlength="200" required>' +
            '<b><label for="updateEmailInp">Email: </label></b>' +
            '<input type="email" name="email" id="updateEmailInp" value="" placeholder="loading..." maxlength="254" required>' +
            '</div>' +
            '<div class="modal-footer">' +
            // '<h3>Modal Footer</h3>' +
            '<button type="button" id="submitBtnEditUser" data-id="" class="submitBtn modalSubmitBtn">Update user</button>' +
            '<div class="clearfix"></div>' +
            '</div>' +
            '</form>' +
            '</div>' +
            '</div>').appendTo($('.usersDiv'));
        $('.modal').show();
        $.ajax({
            url: 'users/' + id,
            type: 'get',
        }).done(function (output) {
            console.log(output);
            let user = JSON.parse(output);
            $('#updateNameInp').val(user.name);
            $('#updateEmailInp').val(user.email);
            $('#submitBtnEditUser').attr('data-id',user.id);
        }).fail(function (output) {
            console.log(output);
            $('.modal').remove();
            alert('Error while retrieving data');
        });
    });

    // Delete user
    $('.usersDiv').on('click', '.delIcon', function () {
        let id = $(this).data('id');
        if (confirm('Are you sure that you want to delete this post?')) {
            $.ajax({
                url: 'users/' + id,
                type: 'delete',
            }).done(function (output) {
                if (output.success === true || output.success === 'true') {
                    $('#user' + id).remove();
                }else{
                    alert('Error while deleting');
                }
            }).fail(function (output) {
                console.log(output);
                alert('Error while deleting');
            });
        }
    });

    // Update user
    $('.usersDiv').on('click', '#submitBtnEditUser', function () {
        let id = $(this).data('id');
        $.ajax({
            url: 'users/' + id,
            // url: 'users',
            type: 'put',
            dataType : "json",
            contentType: "application/json; charset=utf-8",
            data:JSON.stringify({
                email: $('#updateEmailInp').val(),
                name: $('#updateNameInp').val(),
            }),
        }).done(function (output) {
            $('.modal').remove();
            if (output.success === true || output.success === 'true') {
            }else{
                alert('Update: '+output.success);
            }
        }).fail(function (output) {
            console.log(output);
            alert('Error while updating');
        });
    });

    // $('.usersDiv').on('click', '.submitBtnEditUser', function () {
    //     $('#updateNameInp').val()
    //     $('#updateEmailInp').val()
    //
    //     $.ajax({
    //         url: 'users/' + id,
    //         type: 'put',
    //     }).done(function (output) {
    //         if (output === 'success') {
    //             $('#user' + id).remove();
    //         } else {
    //             console.log(output);
    //             alert('Output does not equal the expected string "success"');
    //         }
    //     }).fail(function (output) {
    //         console.log(output);
    //         alert('Error while deleting');
    //     });
    // });


});
