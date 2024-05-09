import {getNoteHtml} from "./client-read-template-note.html.js?v=0.4.2";
import {
    displayClientNoteSkeletonLoader,
    removeClientNoteSkeletonLoader
} from "./client-read-note-skeleton-loader.js?v=0.4.2";
import {fetchData} from "../../general/ajax/fetch-data.js?v=0.4.2";
import {initNotesEventListeners} from "./client-read-note-event-listener-setup.js?v=0.4.2";
import {
    initAutoResizingTextareaElements
} from "../../general/page-component/textarea/auto-resizing-textarea.js?v=0.4.2";
import {scrollToAnchor} from "../../general/page-behaviour/scroll-to-anchor.js?v=0.4.2";
import {fetchTranslations} from "../../general/ajax/fetch-translation-data.js?v=0.4.2";
import {__} from "../../general/general-js/functions.js?v=0.4.2";

/**
 * Loading notes into dom
 * @param {URLSearchParams} queryParams note request query parameters.
 * If not provided, <data id="client-id"> value is taken
 * @param {null|string} noteWrapperId
 */
export function fetchAndLoadClientNotes(queryParams = new URLSearchParams(), noteWrapperId = null) {

    displayClientNoteSkeletonLoader(noteWrapperId);

    // If no query params provided take <data id="client-id"> value
    if (queryParams.toString() === '') {
        let clientId = document.getElementById('client-id').value;
        queryParams.append('client_id', clientId);
    }

    fetchData('notes?' + queryParams.toString()).then(notesFromResponse => {
        removeClientNoteSkeletonLoader(noteWrapperId);
        addNotesToDom(notesFromResponse, noteWrapperId);
        // Script loaded with defer so waiting for DOMContentLoaded is not needed
        initNotesEventListeners();
        // Add note delete btn event listeners
        // The reason it is not in initNotesEventListeners() is that event listener were set up twice and alert modal
        // were displayed one on top of the other and thus not working. Turns out the reason was that I called initAllButtonsAboveNotesEventListeners
        // AND initActivityTextareasEventListeners that already contained initAllDeleteBtnEventListeners
        // initAllButtonsAboveNotesEventListeners();

        // Manually init autoResizingTextareas to include the loaded notes as it's only done during page load and not afterwards
        initAutoResizingTextareaElements();
        scrollToAnchor();
    }).catch(exception => {
        console.error(exception);
        removeClientNoteSkeletonLoader(noteWrapperId);
    });
}


// Get translation for no notes found
let noNotesFound = __('No notes were found');
// Fetch translations and replace str var (fetch done automatically at page loading when imported)
fetchTranslations([noNotesFound]).then(response => {
    // Fill the var with a JSON of the translated words. Key is the original english words and value the translated one
    noNotesFound = response[[noNotesFound]] ?? noNotesFound;
});

/**
 * Add note to page
 *
 * @param {JSON} notes
 * @param {string|null} wrapperId if provided it means that notes are loaded from somewhere else than client read page
 */
export function addNotesToDom(notes, wrapperId = null) {
    let noteContainer = document.getElementById(wrapperId ?? 'client-note-wrapper');

    // If no results, tell user so
    if (notes.length === 0) {
        // document.getElementById('client-activity-personal-info-container').insertAdjacentHTML('afterend', '<br><p id="no-notes-info">No notes were found.</p>')
        noteContainer.insertAdjacentHTML('beforeend', `<p id="no-notes-info">${noNotesFound}.</p>`)
    }

    // Loop over notes and add to DOM
    for (const note of notes) {
        // Client card HTML
        let noteHtml = getNoteHtml(note);
        // If wrapperId is provided and note client name is in response it means that it is not called from
        // client read page and there should be a link to the correct client read page
        if (wrapperId && note.clientFullName) {
            noteHtml = `<a href="clients/${note.clientId}#note-${note.id}-container">${note.clientFullName}</a>` + noteHtml;
        }
        // Add to DOM
        noteContainer.insertAdjacentHTML('beforeend', noteHtml);
    }
}