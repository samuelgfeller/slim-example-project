let addFilterBtn = document.getElementById('add-filter-btn');
let availableFilterDiv = document.getElementById('available-filter-div');
let activeFilterDiv = document.getElementById('active-filter-chips-div');

// Available filter chip collection
addFilterBtn.addEventListener('click', toggleAvailableFilterDiv);

// Hide available filters if click outside of filter area
document.addEventListener('click', event => {
    // If clicked element is no a child of available-filter-div or active-filter-chips-div, hide it
    if (event.target.closest('#available-filter-div') === null &&
        event.target.closest('#active-filter-chips-div') === null) {
        toggleAvailableFilterDiv(true);
    }
});

// Show or hide available filters
function toggleAvailableFilterDiv(hideOnly = false) {
    // hideOnly === false does not work as the function is called by the addFilterBtn event listener which passes the
    // pointer event as first argument
    if (hideOnly !== true && (availableFilterDiv.style.display === 'none' || availableFilterDiv.style.display === '')) {
        availableFilterDiv.style.display = 'inline-block'
    } else {
        availableFilterDiv.style.display = 'none';
    }
}

// Available to active filter chip
// Get filter chips that are not active (in availableFilterDiv)
let availableFilterChips = availableFilterDiv.getElementsByClassName('filter-chip');
for (let inactiveChip of availableFilterChips) {
    inactiveChip.addEventListener('click', moveFilterChipToActive);
}

function moveFilterChipToActive() {
    // Moves div to available list ("this" is the inactiveChip)
    activeFilterDiv.append(this);
    // Add active class
    this.classList.add('filter-chip-active');
    // Remove this event listener
    this.removeEventListener('click', moveFilterChipToActive);
    // Add event listener for chip to be added back to the available collection
    this.addEventListener('click', moveFilterChipToAvailableCollection);
    // Show that there are no more filters if needed
    toggleNoMoreFilters();
}

// Active filter chips
let activeFilterChips = document.getElementsByClassName('filter-chip-active');
for (let activeChip of activeFilterChips) {
    activeChip.addEventListener('click', moveFilterChipToAvailableCollection);
}

function moveFilterChipToAvailableCollection() {
    // Moves div to available list ("this" is the activeChip)
    availableFilterDiv.append(this);
    // Remove active class
    this.classList.remove('filter-chip-active');
    // Remove this event listener
    this.removeEventListener('click', moveFilterChipToAvailableCollection);
    // Add event listener for chip to be added to the active collection
    this.addEventListener('click', moveFilterChipToActive);
    // Hide that there are no more filters if needed
    toggleNoMoreFilters();
}

// Show chip that says that there are no more filters button
function toggleNoMoreFilters() {
    let noMoreFilterSpan = document.getElementById('no-more-available-filters-span');
    console.log(availableFilterDiv.childElementCount);
    // If there is less than one child in the available filter div (1 is the noMoreFilterSpan)
    if (availableFilterDiv.childElementCount === 1) {
        noMoreFilterSpan.style.display = 'inline-block';
        // Hide available filters
        toggleAvailableFilterDiv();
    } else {
        noMoreFilterSpan.style.display = 'none';
    }
}
