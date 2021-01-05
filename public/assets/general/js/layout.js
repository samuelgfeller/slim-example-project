// Navigation bar
// Mobile view
/*
let toggleMenuBtn = document.getElementById("nav-icon");

toggleMenuBtn.addEventListener("click", function () {
    toggleMenuBtn.classList.toggle('open');
});

function toggleMobileView() {
    nav.className === "nav" ? nav.className += " mobile-view" : nav.className = "nav";
}
*/

// Burger icon
let navIcon = document.getElementById('nav-icon');
let nav = document.getElementById("nav");
navIcon.addEventListener("click", function () {
    nav.classList.toggle('open');

    if (nav.className.includes('open')) {
        // If menu collapsed it should old loop over indicators when menu opened
        loopOverIndicators();
    }

});

// Underline
let indicator = document.getElementById('nav-indicator');
let items = document.querySelectorAll('#nav a');

// It is not necessary to position the indicator at page load when menu is collapsed
// 641px is the breakpoint where menu is collapsed (layout.css)
if (window.matchMedia("(min-width: 641px)").matches) {
    // If its a browser and the menu is not collapsed, indicators should directly be looped over
    loopOverIndicators();
}

function loopOverIndicators() {
    items.forEach(function (item, index) {
        item.addEventListener('click', function (e) {
            handleIndicator(e.target);
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

    // Move indicator to clicked menu item or just append it.
    el.appendChild(indicator);
    indicator.style.backgroundColor = 'transparent';
    window.setTimeout(function (){
        // Change indicator to new color
        indicator.style.backgroundColor = el.dataset.activeColor; // "-" become camel case
    }, 10);


    // console.log(document.querySelectorAll('#nav-icon span'));

    // Tint hamburger icon bars to the active color
    document.querySelectorAll('#nav-icon span').forEach(function (bar) {
        bar.backgroundColor = el.dataset.activeColor; // "-" become camel case
    });
    el.classList.add('is-active');
    el.style.color = el.dataset.activeColor;
}
