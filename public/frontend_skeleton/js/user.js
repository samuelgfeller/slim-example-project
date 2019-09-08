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
                $('.postsDiv').empty();
                let places = output;
                // let places = JSON.parse(output);
                $.each(places, function (index, value) {
                    $('<div class="singleBox" data-id="' + value.id + '" id="user' + value.id + '">' +
                        '<div class="boxContent">' +
                        '<div class="boxInnerContent">' +
                        '<img src="frontend_skeleton/img/edit_icon.svg" class="editIcon cursorPointer" alt="edit">' +
                        '<img src="frontend_skeleton/img/del_icon.svg" class="delIcon cursorPointer" alt="del">' +
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
        let id = $('.singleBox').data('id');
        $('<div class="modal" id="myModal">' +
            '<div class="modal-content">' +
            '<div class="modal-header">' +
            '<span class="closeModal">&times;</span>' +
            '<h2>Edit user</h2>' +
            '</div>' +
            '<div class="modal-body">' +
            '<form action="user/change/email" class="blueForm profileForm" method="put" autocomplete="on">' +
            '<label for="updateEmailInp">Email</label>' +
            '<input type="email" name="email" id="updateEmailInp" value="test@test.ch" maxlength="254" required>' +
            '</form>' +
            '</div>' +
            '<div class="modal-footer">' +
            '<h3>Modal Footer</h3>' +
            '<input type="submit" value="Update user">'+
            '</div>' +
            '</div>' +
            '</div>').appendTo($('.usersDiv'));
        $('.modal').show();
        $.ajax({
            url: 'users/' + id,
            type: 'get',
        }).done(function (output) {

        }).fail(function (output) {
            console.log(output);
            alert('Error while deleting');
        });
    });

    $('.usersDiv').on('click', '.delIcon', function () {
        let id = $('.singleBox').data('id');
        if (confirm('Are you sure that you want to delete this post?')) {
            $.ajax({
                url: 'users/' + id,
                type: 'delete',
            }).done(function (output) {
                if (output === 'success') {
                    $('#user' + id).remove();
                } else {
                    console.log(output);
                    alert('Output does not equal the expected string "success"');
                }
            }).fail(function (output) {
                console.log(output);
                alert('Error while deleting');
            });
        }
    });
})
;