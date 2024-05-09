import {getClientNoteSkeletonLoaderHtml} from "./client-read-template-note.html.js?v=0.4.2";

/**
 * Display client note skeleton loaders
 * @param {string|null} wrapperId
 */
export function displayClientNoteSkeletonLoader(wrapperId = null) {
    let noteContainer = document.getElementById(wrapperId ?? 'client-note-wrapper');

    // Display as many skeleton loaders as there are notes
    let notesAmount = noteContainer.dataset.notesAmount;
    if (!notesAmount || notesAmount === '0' || notesAmount === '') {
        notesAmount = 3; // Default
    }
    for (let i = 0; i < notesAmount; i++) {
        noteContainer.insertAdjacentHTML('beforeend', getClientNoteSkeletonLoaderHtml());
    }
}

/**
 * Remove placeholders
 * @param {string|null} wrapperId
 */
export function removeClientNoteSkeletonLoader(wrapperId = null) {
    let noteContainer = document.getElementById(wrapperId ?? 'client-note-wrapper');

    // I had a very strange issue. With getElementsByClassName I got 3 elements but only 2 seem to be looped through
    let skeletonLoaders = noteContainer.querySelectorAll('.client-note-skeleton-loader');
    // Foreach loop over skeleton loaders
    for (let skeletonLoader of skeletonLoaders) {
        // remove from DOM
        skeletonLoader.remove();
    }
}