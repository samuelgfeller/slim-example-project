$(document).ready(function () {

    // Send login requests
    $('#submitBtnLogin').on('click', function () {
        login('loginForm');
    });

    $('#registerSubmitBtn').on('click', function () {
        register('registerForm');
    });


    let registerPassword1Inp = $('#registerPassword1Inp');
    let registerPassword2Inp = $('#registerPassword2Inp');

    $($('#registerPassword1Inp, #registerPassword2Inp')).on('keyup', function () {
        let submitBtn = $('#registerSubmitBtn');
        console.log(inputHaveSameVal(registerPassword1Inp, registerPassword2Inp));
        if (inputHaveSameVal(registerPassword1Inp, registerPassword2Inp)) {
            submitBtn.attr("disabled", false);
        } else {
            submitBtn.attr("disabled", true);
        }
    });

    // Warning that password is unsafe
    $(registerPassword1Inp).on('change', function () {
        // check if warning already appended
        if (isBreached(registerPassword1Inp.val())) {
            if ($('#pwnedPasswordWarning').length === 0) {
                registerPassword1Inp.after('<span class="inputWarning" id="pwnedPasswordWarning">This password is known to have been leaked and is unsafe to use</span>');
            }
        } else {
            $('#pwnedPasswordWarning').remove();
        }
    });


});

/**
 * Checks if given input objects have same values
 * @param inp1
 * @param inp2
 * @returns {boolean}
 */
function inputHaveSameVal(inp1, inp2) {
    return inp1.val() === inp2.val() && inp1.val() !== "";
}

/**
 * Makes request to
 *
 * @param password
 * @returns {boolean}
 */
function isBreached(password) {
    var pwhash = sha1(password);
    var hashprefix = pwhash.substr(0, 5);
    var hashsuffix = pwhash.substr(5);
    let result = false;

    // todo implement async function to return after result https://stackoverflow.com/a/5316805/9013718
    $.ajax({
        url: `https://api.pwnedpasswords.com/range/${hashprefix}`,
        async: false,
        success: function (data) {
            result = data.toLowerCase().includes(hashsuffix);
        }
    });

    return result;
}

/**
 * Leases jwt token and stores in localStorage
 *
 */
function login(formId) {
    if(formIsValid(formId)){
        $.ajax({
            url: config.api_url + 'login',
            type: 'post',
            dataType: "json",
            contentType: "application/json; charset=utf-8",
            data: JSON.stringify({
                email: $('#loginEmailInp').val(),
                password: $('#loginPasswordInp').val(),
            }),
        }).done(function (output) {
            localStorage.setItem('token', output.token);
            $('.loggedInInfo').remove();
            $("#loginFormBox").prepend("<b class='loggedInInfo greenText''>Logged in.</b>");

        }).fail(function (xhr) {
            handleFail(xhr);
        });
    }
}

/**
 * Leases jwt token and stores in localStorage
 *
 */
function register(formId) {
    if (formIsValid(formId)) {
        $.ajax({
            url: config.api_url + 'register',
            type: 'post',
            dataType: "json",
            contentType: "application/json; charset=utf-8",
            data: JSON.stringify({
                name: $('#registerNameInp').val(),
                email: $('#registerEmailInp').val(),
                password: $('#registerPassword1Inp').val(),
                password2: $('#registerPassword2Inp').val(),
            }),
        }).done(function (output) {
            localStorage.setItem('token', output.token);
            $('.loggedInInfo').remove();
            $("#registerFormBox").prepend("<b class='loggedInInfo greenText''>Registered and logged in.</b>");

        }).fail(function (xhr) {
            handleFail(xhr);
        });
    }
}

/**
 * Creates sha1 hash of arbitrary string
 *
 * @param msg
 * @returns {string}
 */
function sha1(msg) {
    function rotate_left(n, s) {
        var t4 = (n << s) | (n >>> (32 - s));
        return t4;
    }

    function lsb_hex(val) {
        var str = '';
        var i;
        var vh;
        var vl;
        for (i = 0; i <= 6; i += 2) {
            vh = (val >>> (i * 4 + 4)) & 0x0f;
            vl = (val >>> (i * 4)) & 0x0f;
            str += vh.toString(16) + vl.toString(16);
        }
        return str;
    }

    function cvt_hex(val) {
        var str = '';
        var i;
        var v;
        for (i = 7; i >= 0; i--) {
            v = (val >>> (i * 4)) & 0x0f;
            str += v.toString(16);
        }
        return str;
    }

    function Utf8Encode(string) {
        string = string.replace(/\r\n/g, '\n');
        var utftext = '';
        for (var n = 0; n < string.length; n++) {
            var c = string.charCodeAt(n);
            if (c < 128) {
                utftext += String.fromCharCode(c);
            } else if ((c > 127) && (c < 2048)) {
                utftext += String.fromCharCode((c >> 6) | 192);
                utftext += String.fromCharCode((c & 63) | 128);
            } else {
                utftext += String.fromCharCode((c >> 12) | 224);
                utftext += String.fromCharCode(((c >> 6) & 63) | 128);
                utftext += String.fromCharCode((c & 63) | 128);
            }
        }
        return utftext;
    }
    var blockstart;
    var i, j;
    var W = new Array(80);
    var H0 = 0x67452301;
    var H1 = 0xEFCDAB89;
    var H2 = 0x98BADCFE;
    var H3 = 0x10325476;
    var H4 = 0xC3D2E1F0;
    var A, B, C, D, E;
    var temp;
    msg = Utf8Encode(msg);
    var msg_len = msg.length;
    var word_array = [];
    for (i = 0; i < msg_len - 3; i += 4) {
        j = msg.charCodeAt(i) << 24 | msg.charCodeAt(i + 1) << 16 |
            msg.charCodeAt(i + 2) << 8 | msg.charCodeAt(i + 3);
        word_array.push(j);
    }
    switch (msg_len % 4) {
        case 0:
            i = 0x080000000;
            break;
        case 1:
            i = msg.charCodeAt(msg_len - 1) << 24 | 0x0800000;
            break;
        case 2:
            i = msg.charCodeAt(msg_len - 2) << 24 | msg.charCodeAt(msg_len - 1) << 16 | 0x08000;
            break;
        case 3:
            i = msg.charCodeAt(msg_len - 3) << 24 | msg.charCodeAt(msg_len - 2) << 16 | msg.charCodeAt(msg_len - 1) << 8 | 0x80;
            break;
    }
    word_array.push(i);
    while ((word_array.length % 16) != 14) word_array.push(0);
    word_array.push(msg_len >>> 29);
    word_array.push((msg_len << 3) & 0x0ffffffff);
    for (blockstart = 0; blockstart < word_array.length; blockstart += 16) {
        for (i = 0; i < 16; i++) W[i] = word_array[blockstart + i];
        for (i = 16; i <= 79; i++) W[i] = rotate_left(W[i - 3] ^ W[i - 8] ^ W[i - 14] ^ W[i - 16], 1);
        A = H0;
        B = H1;
        C = H2;
        D = H3;
        E = H4;
        for (i = 0; i <= 19; i++) {
            temp = (rotate_left(A, 5) + ((B & C) | (~B & D)) + E + W[i] + 0x5A827999) & 0x0ffffffff;
            E = D;
            D = C;
            C = rotate_left(B, 30);
            B = A;
            A = temp;
        }
        for (i = 20; i <= 39; i++) {
            temp = (rotate_left(A, 5) + (B ^ C ^ D) + E + W[i] + 0x6ED9EBA1) & 0x0ffffffff;
            E = D;
            D = C;
            C = rotate_left(B, 30);
            B = A;
            A = temp;
        }
        for (i = 40; i <= 59; i++) {
            temp = (rotate_left(A, 5) + ((B & C) | (B & D) | (C & D)) + E + W[i] + 0x8F1BBCDC) & 0x0ffffffff;
            E = D;
            D = C;
            C = rotate_left(B, 30);
            B = A;
            A = temp;
        }
        for (i = 60; i <= 79; i++) {
            temp = (rotate_left(A, 5) + (B ^ C ^ D) + E + W[i] + 0xCA62C1D6) & 0x0ffffffff;
            E = D;
            D = C;
            C = rotate_left(B, 30);
            B = A;
            A = temp;
        }
        H0 = (H0 + A) & 0x0ffffffff;
        H1 = (H1 + B) & 0x0ffffffff;
        H2 = (H2 + C) & 0x0ffffffff;
        H3 = (H3 + D) & 0x0ffffffff;
        H4 = (H4 + E) & 0x0ffffffff;
    }
    var temp = cvt_hex(H0) + cvt_hex(H1) + cvt_hex(H2) + cvt_hex(H3) + cvt_hex(H4);

    return temp.toLowerCase();
}
