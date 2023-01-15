import {getNoteHtml} from "./client-read-template-note.html.js?v=0.2.0";
import {
    displayClientNoteLoadingPlaceholder,
    removeClientNoteContentPlaceholder
} from "./client-read-note-loading-placeholder.js?v=0.2.0";
import {fetchData} from "../../general/ajax/fetch-data.js?v=0.2.0";
import {initNotesEventListeners} from "./client-read-note-event-listener-setup.js?v=0.2.0";
import {initAutoResizingTextareas} from "../../general/page-component/textarea/auto-resizing-textarea.js?v=0.2.0";
import {scrollToAnchor} from "../../general/page-behaviour/scroll-to-anchor.js?v=0.2.0";

/**
 * Loading notes into dom
 * @param {URLSearchParams} queryParams note request query parameters.
 * If not provided, <data id="client-id"> value is taken
 * @param {null|string} noteWrapperId
 */
export function fetchAndLoadClientNotes(queryParams = new URLSearchParams(), noteWrapperId = null) {

    displayClientNoteLoadingPlaceholder(noteWrapperId);

    let redirectIfUnauthenticatedUrl = '';
    // If no query params provided take <data id="client-id"> value
    if (queryParams.toString() === '') {
        let clientId = document.getElementById('client-id').value;
        queryParams.append('client_id', clientId);
        redirectIfUnauthenticatedUrl = "client/" + clientId
    }

    fetchData('notes?' + queryParams.toString(), redirectIfUnauthenticatedUrl)
        .then(notesFromResponse => {
            removeClientNoteContentPlaceholder(noteWrapperId);
            addNotesToDom(notesFromResponse, noteWrapperId);
            // Script loaded with defer so waiting for DOMContentLoaded is not needed
            initNotesEventListeners();
            // Add note delete btn event listeners
            // The reason it is not in initNotesEventListeners() is that event listener were set up twice and alert modal
            // were displayed one on top of the other and thus not working. Turns out the reason was that I called initAllButtonsAboveNotesEventListeners
            // AND initActivityTextareasEventListeners that already contained initAllDeleteBtnEventListeners
            // initAllButtonsAboveNotesEventListeners();

            // Manually init autoResizingTextareas to include the loaded notes as it's only done during page load and not afterwards
            initAutoResizingTextareas();
            scrollToAnchor();
        });
}


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
        noteContainer.insertAdjacentHTML('beforeend', '<p id="no-notes-info">No notes were found.</p>')
    }

    // Loop over notes and add to DOM
    for (const note of notes) {
        // Client card HTML
        let noteHtml = getNoteHtml(note);
        // If wrapperId is provided and note client name is in response it means that it is not called from
        // client read page and there should be a link to the correct client read page
        if (wrapperId && note.clientFullName) {
            noteHtml = `<a href="clients/${note.clientId}#note-${note.id}-container">Client ${note.clientFullName}</a>` + noteHtml;
        }
        // Add to DOM
        noteContainer.insertAdjacentHTML('beforeend', noteHtml);
    }
}