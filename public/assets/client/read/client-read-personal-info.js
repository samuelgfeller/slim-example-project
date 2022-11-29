let existingValuesContainer = document.querySelector('#client-personal-info-flex-container');
// Icons that have a value set
let existingIcons = existingValuesContainer.querySelectorAll('.personal-info-icon');
// Add new personal info - div does not exist in dom if user has not rights
let availablePersonalInfoIconsDiv = document.querySelector('#add-client-personal-info-div');
// Icons that don't have a value for the client
let availableIconsIncludingTrashBin = availablePersonalInfoIconsDiv?.querySelectorAll('.personal-info-icon');
let availableIcons = availablePersonalInfoIconsDiv?.querySelectorAll('.personal-info-icon:not(#delete-client-btn)');
let availableIconsAmount = availableIconsIncludingTrashBin?.length;
let initialNewIconWidth = availablePersonalInfoIconsDiv?.offsetWidth;

export function addIconToAvailableDiv(availableIcon, containerToHide = null){
    if (containerToHide !== null) {
        containerToHide.style.display = 'none';
    }
    // Show as available icon (if hideIfEmpty is true, the available icon should never be "null")
    availableIcon.style.display = null;
    // Reload whole div as there were changes made
    loadAvailablePersonalInfoIconsDiv();
}
export function removeIconFromAvailableDiv(availableIcon){
    availableIcon.style.display = 'none';
    loadAvailablePersonalInfoIconsDiv();
}

/**
 * Determine how many icons should be visible in the
 * personal info available icons container and
 * calculate hover styling
 */
export function loadAvailablePersonalInfoIconsDiv() {
    if (availableIcons !== undefined) {
        // Make map of only alt attributes if parent has not display none
        let existingIconsMap = new Map(Array.from(existingIcons).map(obj => obj.parentNode.style.display !== 'none' ? [obj.alt, obj.alt] : []));
        // Convert map into array containing only strings of the alt attribute (with keys would be `([key, value]) =>({key, value})`)
        let existingIconsFiltered = Array.from(existingIconsMap, ([value]) => (value));

        // Reset available icons amount in case this function is called after page load
        availableIconsAmount = availableIconsIncludingTrashBin.length;
        for (let availableIcon of availableIcons) {
            // Hide icon from new list if it exists already
            if (existingIconsFiltered.includes(availableIcon.alt)) {
                availableIcon.style.display = 'none';
                availableIconsAmount -= 1;
            }
            // Add event listener to each available icon
            availableIcon.addEventListener('click', addPersonalInfoIconToExisting);
        }
        if (availableIconsAmount === 0) {
            availablePersonalInfoIconsDiv.style.display = 'none';
        } else {
            availablePersonalInfoIconsDiv.style.display = null;
            // Open the div with the correct width on hover
            openCloseAvailablePersonalIconsEventSetup();
        }
    }
}

/**
 * Move personal info and prepare entry in existing container
 */
function addPersonalInfoIconToExisting() {
    // "this" is the available icon
    let fieldContainer = document.querySelector('#' + this.alt + '-container');
    fieldContainer.style.display = null;
    fieldContainer.querySelector('.contenteditable-edit-icon').click();
}

/**
 * Open and close available personal info icons
 *
 * Browsers unfortunately don't support transition on auto width or height elements so the new max width has to be
 * calculated in js to have an absolute value for the transition to work
 * https://github.com/w3c/csswg-drafts/issues/626
 * https://css-tricks.com/using-css-transitions-auto-dimensions/
 */
function openCloseAvailablePersonalIconsEventSetup() {
    // Re calculate max with of available container
    initialNewIconWidth = availablePersonalInfoIconsDiv.offsetWidth;
    // Calculate available personal info icons div max width
    availablePersonalInfoIconsDiv.addEventListener('mouseover', openAvailableIconsDiv);
    availablePersonalInfoIconsDiv.addEventListener('mouseout', closeAvailableIconsDiv);
}

function openAvailableIconsDiv() {
    // The first icon (plus button to slide open available icons div) has to have the exact same width and padding than the others
    let oneIconWidth = availablePersonalInfoIconsDiv.querySelector('#toggle-personal-info-icons').offsetWidth;
    let newIconsContainerWidthWithoutIcons = initialNewIconWidth - oneIconWidth;
    // max-width is the first icon that is always displayed plus the total amount times the width of one icon and adding container
    availablePersonalInfoIconsDiv.style.maxWidth = ((1 + availableIconsAmount) * oneIconWidth) + newIconsContainerWidthWithoutIcons + 5 + 'px';
}

function closeAvailableIconsDiv() {
    availablePersonalInfoIconsDiv.style.maxWidth = initialNewIconWidth + 'px';
}