window.onload = function () {
    let elements = document.getElementsByClassName("noAnimationOnPageLoad");
    // elements is a HTMLCollection and does not have forEach method. It has to be converted as array before
    Array.from(elements).forEach(function (element) {
        element.classList.remove("noAnimationOnPageLoad");
    });
}
