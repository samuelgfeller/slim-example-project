// Navigation bar
// let nav = document.querySelector("nav");
let nav = document.getElementById("nav-container");
let items = document.querySelectorAll('nav a');

// Cannot be passed as an argument when calling loopOverItems since on resize event listeners are added
// multiple times on resize and there is a bug when click event calls handleIndicator with isMobile true [SLE-63]
let isMobile = true;

// Cannot use entire nav because then it collapses on each click on a menu element since its also in nav
document.getElementById("nav-mobile-toggle-icon").addEventListener("click", toggleMobileNav);

function toggleMobileNav() {
    nav.classList.toggle('open');
    if (nav.className.includes('open')) {
        isMobile = true;
    }
}

// Fix for indicator glitch at page load
window.addEventListener("load",function(event) {
// At 961px the menu is in desktop version and not collapsed.
    if (window.matchMedia("(min-width: 961px)").matches) {
        isMobile = false;
    }
});

window.onresize = function () {
    let oldIsMobile = isMobile;

    isMobile = !window.matchMedia("(min-width: 961px)").matches;

    // Only if previously isMobile had a different value
    if (oldIsMobile !== isMobile) {
        if (isMobile === false) {
            // If menu was open close it
            nav.classList.remove("open");
        }
    }
};
