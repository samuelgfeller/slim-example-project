window.onload = function () {
    let elements = document.getElementsByClassName("no-animation-on-page-load");
    // elements is a HTMLCollection and does not have forEach method. It has to be converted as array before
    Array.from(elements).forEach(function (element) {
        element.classList.remove("no-animation-on-page-load");
    });
}
