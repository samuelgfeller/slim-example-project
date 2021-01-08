// Navigation bar
let nav = document.getElementById("nav");
let indicator = document.getElementById('nav-indicator');
let items = document.querySelectorAll('#nav a');

// Cannot use entire nav because then it collapses on each click on a menu element since its also in nav
document.getElementById("nav-icon").addEventListener("click", toggleMobileNav);
document.getElementById("brand-name-span").addEventListener("click", toggleMobileNav);

// Cannot be passed as an argument when calling loopOverItems since on resize event listeners are added
// multiple times on resize and there is a bug when click event calls handleIndicator with isMobile true [SLE-63]
let isMobile = true;

// At 1025px the menu is in desktop version and not collapsed.
if (window.matchMedia("(min-width: 1025px)").matches) {
    isMobile = false;
    loopOverItems();
}

window.addEventListener('resize', function () {
    let oldIsMobile = isMobile;

    isMobile = !window.matchMedia("(min-width: 1025px)").matches;

    if (oldIsMobile !== isMobile) {
        if (isMobile === false) {
            // If menu was open close it
            nav.classList.remove("open");
            // Move indicator back to nav
            nav.appendChild(indicator);
        }

        // Prevent to take mobile style to desktop or vice versa
        // CSS style is overwritten by set element style from handleIndicator function
        indicator.removeAttribute('style');
        loopOverItems();
    }
});

function toggleMobileNav() {
    nav.classList.toggle('open');

    if (nav.className.includes('open')) {
        isMobile = true;
        // If menu collapsed it should old loop over indicators when menu opened
        loopOverItems();
    }
}

function loopOverItems() {
    items.forEach(function (item, index) {
        item.addEventListener('click', function (e) {
            handleIndicator(e.target)
        });

        // If contains is active, execute function
        item.classList.contains('is-active') && handleIndicator(item);
    });
}

function handleIndicator(el) {
    items.forEach(function (item) {
        item.classList.remove('is-active');
        item.removeAttribute('style');
    });

    if (isMobile === true) {
        // Move indicator to clicked menu item or just append it.
        el.appendChild(indicator);
        indicator.style.backgroundColor = 'transparent';
        window.setTimeout(function () {
            // Change indicator to new color
            indicator.style.backgroundColor = el.dataset.activeColor; // "-" become camel case
        }, 10);

        // Tint hamburger icon bars to the active color
        document.querySelectorAll('#nav-icon span').forEach(function (bar) {
            bar.style.background = el.dataset.activeColor; // "-" become camel case
        });
    } else {
        indicator.style.width = "".concat(el.offsetWidth, "px");
        indicator.style.left = "".concat(el.offsetLeft, "px");
        indicator.style.backgroundColor = el.dataset.activeColor; // "-" become camel case
    }

    el.classList.add('is-active');
    el.style.color = el.dataset.activeColor;
}
