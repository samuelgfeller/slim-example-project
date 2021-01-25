window.addEventListener("load",function(event) {
    /** Class with no animation on page load */
    let elements = document.getElementsByClassName("no-animation-on-page-load");
    // elements is a HTMLCollection and does not have forEach method. It has to be converted as array before
    Array.from(elements).forEach(function (element) {
        element.classList.remove("no-animation-on-page-load");
    });

    /** Page height - mobile*/
    // vh without scrollbar on mobile https://css-tricks.com/the-trick-to-viewport-units-on-mobile/
    // First we get the viewport height and we multiple it by 1% to get a value for a vh unit
    let vh = window.innerHeight * 0.01;
    // Then we set the value in the --vh custom property to the root of the document
    document.documentElement.style.setProperty('--vh', vh + 'px');

    /** Flash messages */
    let flashes = document.getElementsByClassName("flash");
    Array.from(flashes).forEach(function(flash, index) {
        if (index === 0){
            // Add first without timeout
            flash.className += ' slide-in'
        }else {
            setTimeout(function () {
                flash.className += ' slide-in'
            }, 1000);
        }
        let closeBtn = flash.querySelector('.close-btn');
        closeBtn.addEventListener('click', function () {
            flash.className = flash.className.replace('slide-in', "slide-out");
        });

        setTimeout(function () {
            flash.className = flash.className.replace('slide-in', "slide-out");
        }, 7000);
    });
});
