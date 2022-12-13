let addFilterBtn = document.getElementById('add-filter-btn');
let availableFilterDiv = document.getElementById('available-filter-div');
// Currently only one filter chip system is possible in dom - still using class instead of id because it is used
// in dashboard to filter users, and it also has client panels
let activeFilterDiv = document.querySelector('.active-filter-chips-div');

// Available to active filter chip
export function initFilterChipEventListeners(chipClickEventHandler) {
    // Button to open available filter chip collection
    addFilterBtn.addEventListener('click', toggleAvailableFilterDiv);

    // Show no more filter span if there are none on initialisation
    toggleNoMoreFilters();

    // Get active and inactive filter chips
    let filterChips = document.querySelectorAll('.filter-chip');
    for (let filterChip of filterChips) {
        filterChip.addEventListener('click', toggleFilterChip);
        filterChip.addEventListener('click', chipClickEventHandler);
    }

    // Hide available filters if click outside of filter area
    document.addEventListener('click', event => {
        // If clicked element is not a child of available-filter-div or active-filter-chips-div, hide it
        if (event.target.closest('#available-filter-div') === null && event.target.closest('#add-filter-btn') === null) {
            toggleAvailableFilterDiv(true);
        }
    });
}

// Show or hide available filters container
function toggleAvailableFilterDiv(hideOnly = false) {
    // hideOnly === false does not work as the function is called by the addFilterBtn event listener which passes the
    // pointer event as first argument
    if (hideOnly !== true && (availableFilterDiv.style.display === 'none' || availableFilterDiv.style.display === '')) {
        // Show filter div
        availableFilterDiv.style.display = 'inline-block'
        addFilterBtn.textContent = addFilterBtn.textContent.replace('+', '-');
    } else {
        // Hide filter div
        availableFilterDiv.style.display = 'none';
        addFilterBtn.textContent = addFilterBtn.textContent.replace('-', '+');
    }
}

/**
 * Add chip to available collection or from available to active
 * "this" is the .filter-chip
 */
function toggleFilterChip() {
    const category = this.querySelector('span').dataset.category;
    let categoryTitle = availableFilterDiv
        .querySelector(`.filter-chip-container-label[data-category="${category}"]`);
    // If filter chip is in available div
    if (this.closest('#available-filter-div')) {
        // Moves div to active list ("this" is the inactiveChip)
        activeFilterDiv.append(this);
        // Add active class
        this.classList.add('filter-chip-active');
    } else {
        // Moves div to available list below the right category ("this" is the activeChip)
        if (category !== '') {
            // If category title doesn't exist in available div (happens when all filters of category are in use)
            if (categoryTitle === null) {
                // Insert category title
                availableFilterDiv.insertAdjacentHTML('beforeend', `<span 
                    class='filter-chip-container-label' data-category='${category}'>${category}</span>`);
                categoryTitle = availableFilterDiv
                    .querySelector(`.filter-chip-container-label[data-category="${category}"]`);
            }
            categoryTitle.after(this);
        } else {
            // Insert before first category title (if none exist, it will still be added)
            availableFilterDiv.insertBefore(this, availableFilterDiv.querySelector(`.filter-chip-container-label`));
        }
        // Remove active class
        this.classList.remove('filter-chip-active');
    }
    // Check if elements with current category exists in available, if yes show title otherwise hide it
    if (categoryTitle) {
        if (availableFilterDiv.querySelector(`.filter-chip span[data-category="${category}"]`)) {
            // Making sure that category span is displayed by removing display property if set
            categoryTitle.style.display = null;
        } else {
            categoryTitle.style.display = 'none';
        }
    }

    // Show / hide message that there are no more filters if needed
    toggleNoMoreFilters();
}

// Show chip that says that there are no more filters button
function toggleNoMoreFilters() {
    let noMoreFilterSpan = document.getElementById('no-more-available-filters-span');
    // If there is less than one child in the available filter div (1 is the noMoreFilterSpan)
    if (availableFilterDiv.querySelector(`.filter-chip`)) {
        noMoreFilterSpan.style.display = 'none';
    } else {
        noMoreFilterSpan.style.display = 'inline-block';
        // Hide available filters if not already hidden
        toggleAvailableFilterDiv(true);
    }
}
