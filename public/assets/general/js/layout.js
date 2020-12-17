
// Navigation bar
let toggleMenuBtn = document.getElementById("toggleMenuBtn");

toggleMenuBtn.addEventListener("click", toggleMobileView);


function toggleMobileView() {
    let x = document.getElementById("nav");
    if (x.className === "nav") {
        x.className += " mobile-view";
    } else {
        x.className = "nav";
    }
}

let indicator = document.querySelector('.nav-indicator');
let items = document.querySelectorAll('.nav a');

items.forEach(function (item, index) {
    item.addEventListener('click', function (e) {
        handleIndicator(e.target);
    });
    // If contains is active, execute function
    item.classList.contains('is-active') && handleIndicator(item);
});

function handleIndicator(el) {
    items.forEach(function (item) {
        item.classList.remove('is-active');
        item.removeAttribute('style');
    });
    indicator.style.width = "".concat(el.offsetWidth, "px");
    indicator.style.left = "".concat(el.offsetLeft, "px");
    indicator.style.backgroundColor = el.dataset.activeColor; // "-" become camel case
    el.classList.add('is-active');
    el.style.color = el.getAttribute('active-color');
}