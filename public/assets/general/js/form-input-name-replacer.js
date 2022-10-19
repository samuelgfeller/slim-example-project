/**
 * Desktop browsers (unlike mobile) mess up the autocomplete suggestion box placement
 * if the input field moves down when clicking on them.
 * This script attempts to correct that by removing the "name" attribute and then placing
 * it again after the full animation.
 *
 * More details and source: https://stackoverflow.com/q/71913460/9013718
 */

const inputGroups = document.querySelectorAll('.form-input-group');

// removed because the 'transitionend' animation isn't picked up on the firefox browser and thus the names are never added again
// inputGroups.forEach(inputGroup => {
//     const input = inputGroup.querySelector('input');
//
//     // Remove name on page load
//     if (input.name) {
//         removeName(input);
//     }
//
//     inputGroup.addEventListener('transitionend', (e) => {
//         if (e.propertyName === "transform") {
//             if (input.name) {
//                 removeName(input);
//             } else {
//                 input.name = input.dataset.name;
//                 input.autocomplete = input.dataset.name;
//                 input.dataset.name = '';
//             }
//         }
//     });
// });

function removeName(input) {
    input.dataset.name = input.name;
    input.name = '';
    input.autocomplete = '';
}
