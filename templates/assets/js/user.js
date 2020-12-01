$(document).ready(function () {

    loadAllUsers();

    // Edit user open form modal
    $('#usersDiv').on('click', '.editIcon', function () {
        let id = $(this).data('id');
        openEditUserForm(id);
    });

    // Send update request
    $('#usersDiv').on('click', '#submitBtnEditUser', function () {
        let id = $(this).data('id');
        submitUpdatedUser(id);
    });

    // Delete user
    $('#usersDiv').on('click', '.delIcon', function () {
        let id = $(this).data('id');
        deleteUser(id);
    });
});


/**
 * Populate #usersDiv with all users in database
 */
function loadAllUsers() {
    //Load users
    $.ajax({
        url: config.api_url + 'users',
        dataType: "json",
        type: 'get',
        beforeSend: function (xhr) {
            /* Authorization header */
            xhr.setRequestHeader("Authorization", "Bearer " + localStorage.getItem("token"));
        },
        // Other way to send header:
        // headers: {'Authorization' : 'Bearer 12345'},
    }).done(function (output) {
        // output = JSON.parse(output);
        if (output !== '') {
            if (output === '[]') {
            } else {
                let users = output;
                // let places = JSON.parse(output);
                $('#usersDiv').empty();
                $.each(users, function (index, value) {
                    getUserBox(value).appendTo($('#usersDiv'));
                });
            }
        } else {
            console.log(output);
        }
    }).fail(function (xhr) {
        $('#usersDiv').empty();
        handleFail(xhr);
    });
}

function getUserBox(jsonData) {
    return $('<div class="singleBox" id="user' + jsonData.id + '">' +
        '<div class="boxContent">' +
        '<img src="/img/edit_icon.svg" class="editIcon cursorPointer" data-id="' + jsonData.id + '" alt="edit">' +
        '<img src="/img/del_icon.svg" class="delIcon cursorPointer" data-id="' + jsonData.id + '" alt="del">' +
        '<div class="loader" id="loaderForUser' + jsonData.id + '"></div>' +
        '<h3 class="boxHeader">' + jsonData.name + '</h3>' +
        '<div id="boxInnerContent' + jsonData.id + '">' +
        '<p><span class="infoInBoxSpan">Email: </span><b>' + jsonData.email + '</b></p>' +
        '<p><span class="infoInBoxSpan">Updated at: </span><b>' + jsonData.updated_at + '</b></p>' +
        '<p><span class="infoInBoxSpan">Created at: </span>' + jsonData.created_at + '</p>' +
        '</div>' +
        '</div>' +
        '</div>');
}

/**
 * Open Modalbox with form to edit the user data
 *
 * @param id
 */
function openEditUserForm(id) {
    let header = '<h2>Edit user</h2>';
    let body = '<form action="users/' + id + '" class="blueForm modalForm" autocomplete="on">' +
        '<b><label for="updateNameInp">Name: </label></b>' +
        '<input type="text" name="name" id="updateNameInp" value="" placeholder="loading..." maxlength="200" required>' +
        '<b><label for="updateEmailInp">Email: </label></b>' +
        '<input type="email" name="email" id="updateEmailInp" value="" placeholder="loading..." maxlength="254" required>';
    let footer = '<button type="button" id="submitBtnEditUser" data-id="" class="submitBtn modalSubmitBtn">Update user</button>' +
        '<div class="clearfix"></div>' +
        '</form>';

    createModal(header, body, footer, $('#usersDiv'));

    // Retrieve actual user infos and populate input
    $.ajax({
        dataType: "json",
        url: config.api_url + 'users/' + id,
        type: 'get',
        beforeSend: function (xhr) {
            /* Authorization header */
            xhr.setRequestHeader("Authorization", "Bearer " + localStorage.getItem("token"));
        },
    }).done(function (output) {
        let user = output;
        $('#updateNameInp').val(user.name);
        $('#updateEmailInp').val(user.email);
        $('#submitBtnEditUser').attr('data-id', user.id);
    }).fail(function (xhr) {
        closeModal();
        handleFail(xhr);
    });
}

/**
 * Send form data via put to update an user
 *
 * @param id
 */
function submitUpdatedUser(id) {
    $.ajax({
        url: config.api_url + 'users/' + id,
        // url: 'users',
        type: 'put',
        dataType: "json",
        contentType: "application/json; charset=utf-8",
        beforeSend: function (xhr) {
            /* Authorization header */
            xhr.setRequestHeader("Authorization", "Bearer " + localStorage.getItem("token"));
        },
        data: JSON.stringify({
            email: $('#updateEmailInp').val(),
            name: $('#updateNameInp').val(),
        }),
    }).done(function (output) {
        closeModal();
        if (output.status === 'success') {
            showLoader('loaderForUser' + id);
            reloadUser(id);
        } else {
            alert('Update: ' + output.success);
        }
    }).fail(function (xhr) {
        handleFail(xhr);

    });
}

/**
 * Send request to delete an user
 *
 * @param id
 */
function deleteUser(id) {
    if (confirm('Are you sure that you want to delete this user?')) {
        $.ajax({
            url: config.api_url + 'users/' + id,
            type: 'delete',
            beforeSend: function (xhr) {
                /* Authorization header */
                xhr.setRequestHeader("Authorization", "Bearer " + localStorage.getItem("token"));
            },
        }).done(function (output) {
            if (output.status === 'success') {
                $('#user' + id).remove();
            } else {
                alert('Error while deleting');
            }
        }).fail(function (xhr) {
            handleFail(xhr);
        });
    }
}

function reloadUser(id) {
    $.ajax({
        dataType: "json",
        url: config.api_url + 'users/' + id,
        type: 'get',
        beforeSend: function (xhr) {
            /* Authorization header */
            xhr.setRequestHeader("Authorization", "Bearer " + localStorage.getItem("token"));
        },
    }).done(function (output) {
        let user = output;
        console.log(output);
        hideLoader('loaderForUser' + id);
        $('#user' + id).replaceWith(getUserBox(user))
    }).fail(function (xhr) {
        handleFail(xhr);
    });
}
