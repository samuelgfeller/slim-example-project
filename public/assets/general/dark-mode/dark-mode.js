// Get the toggle switch element
const toggleSwitch = document.querySelector('#dark-mode-toggle-checkbox');

// Retrieve the current theme from localStorage
const currentTheme = localStorage.getItem('theme') ? localStorage.getItem('theme') : null;

// Set the theme based on the stored value from localStorage
if (currentTheme) {
    // Set the data-theme attribute on the root element
    document.documentElement.setAttribute('data-theme', currentTheme);

    // Check the toggle switch if the current theme is 'dark'
    if (currentTheme === 'dark') {
        toggleSwitch.checked = true;
    }
}

// Add event listener to the toggle switch for theme switching
toggleSwitch.addEventListener('change', switchTheme, false);

// Set the theme on initial load if there is a stored value in localStorage
if (currentTheme) {
    // Set the data-theme attribute on the root element
    document.documentElement.setAttribute('data-theme', currentTheme);

    // If the current theme is 'dark', set the data-theme attribute to 'dark'
    if (currentTheme === 'dark') {
        document.documentElement.setAttribute('data-theme', 'dark');
    }
}

/**
 * Handle theme switching with localstorage
 *
 * @param e
 */
function switchTheme(e) {
    // Check the current theme and switch to the opposite theme
    if (document.documentElement.getAttribute('data-theme') === 'dark') {
        document.documentElement.setAttribute('data-theme', 'light');
        localStorage.setItem('theme', 'light');
    } else {
        document.documentElement.setAttribute('data-theme', 'dark');
        localStorage.setItem('theme', 'dark');
    }
}


