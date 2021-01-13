window.onload = function () {
    // No animation on page load
    let elements = document.getElementsByClassName("no-animation-on-page-load");
    // elements is a HTMLCollection and does not have forEach method. It has to be converted as array before
    Array.from(elements).forEach(function (element) {
        element.classList.remove("no-animation-on-page-load");
    });

    // vh without searchbar on mobile https://css-tricks.com/the-trick-to-viewport-units-on-mobile/
    // First we get the viewport height and we multiple it by 1% to get a value for a vh unit
    let vh = window.innerHeight * 0.01;
    // Then we set the value in the --vh custom property to the root of the document
    document.documentElement.style.setProperty('--vh', vh + 'px');
}
