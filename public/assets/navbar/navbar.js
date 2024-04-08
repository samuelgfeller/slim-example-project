// Navigation bar
let nav = document.getElementById("nav-container");
let items = document.querySelectorAll('nav a');

let isMobile = true;

// Cannot use the entire nav because then it collapses on each click on a menu element since its also in nav
document.getElementById("nav-mobile-toggle-icon").addEventListener("click", toggleOpenCloseMobileNav);

function toggleOpenCloseMobileNav() {
    nav.classList.toggle('open');
    if (nav.className.includes('open')) {
        isMobile = true;
    }
}

// Remove "open" class from nav when the window is expanded to desktop size
window.onresize = function () {
    let previousIsMobile = isMobile;

    isMobile = !window.matchMedia("(min-width: 961px)").matches;

    // Only if previously isMobile had a different value
    if (previousIsMobile !== isMobile) {
        if (isMobile === false) {
            // If menu was open close it
            nav.classList.remove("open");
        }
    }
};
