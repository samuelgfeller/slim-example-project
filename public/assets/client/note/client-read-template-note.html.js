import {html} from "../../general/general-js/functions.js?v=0.4.1";

export function getNoteHtml(note) {
    // Thanks https://www.youtube.com/watch?v=Mus_vwhTCq0 for this syntax
    const {id, createdAt, privilege, hidden, userFullName, message, isClientMessage} = note;

    // ANY NOTE HTML THAT IS CHANGED BELOW HAS TO ADAPTED IN client-read-create-note.js AS WELL
    // (addNewNoteTextarea, populateNewNoteDomAttributes)
    return `<div id="note-${id}-container" 
              class="note-container ${hidden === 1 || hidden === '1' ? 'hidden-note' : ''}">
                <label for="note-${id}" data-note-id="${id}" class="bigger-select-label textarea-label">
                    <a href="${window.location.href}#note-${id}-container" 
                        class="note-left-side-label no-style-a">${createdAt}</a>
                    ${/*Show active eye icon if hidden*/ hidden === 1 || hidden === '1' ? `<img 
                        class="btn-above-note hide-note-btn ${privilege.includes('U') ? `` : `
                            not-clickable` /*Add not clickable class when not allowed to update*/}" alt="hide" 
                        style="display: inline-block" src="assets/client/img/eye-icon-active.svg"
                        >` : /* Else the non-active one if allowed*/ privilege.includes('U') ? `
                        <img class="btn-above-note hide-note-btn" alt="hide" src="assets/client/img/eye-icon.svg">` : ''}
                    ${/*Show delete button */ privilege.includes('D') ? `<img 
                        class="btn-above-note delete-note-btn" alt="delete" src="assets/general/general-img/action/del-btn-icon.svg">` : ''}
                    <span class="subdued-text note-right-side-label-span 
                    ${isClientMessage === 1 ? 'client-message-label' : ''}">${html(userFullName)}</span>
                </label>
                <!-- Extra div necessary to position circle loader to relative parent without taking label into account -->
                <div class="relative">
                   ${privilege.includes('R') ? '' : `<div class="hidden-textarea-overlay"></div>`}
                    <!-- Textarea opening and closing has to be on the same line to prevent unnecessary line break -->
                    <textarea class="auto-resize-textarea ${isClientMessage === 1 ? 'client-message-textarea' : ''}
                      ${/* Blur note text if not allowed to read*/ privilege.includes('R') ? `
                      ` : 'hidden-note-message'}"
                              id="note-${id}"
                              data-note-id="${id}"
                              minlength="4" maxlength="1000" required
                              data-editable="${privilege.includes('U') ? '1' : '0'}"
                              name="message">${html(message)}</textarea>
                    <div class="circle-loader client-note" data-note-id="${id}">
                        <div class="checkmark draw"></div>
                    </div>
                </div>
            </div>`;
}

export function getClientNoteSkeletonLoaderHtml() {
    return `<div class="client-note-skeleton-loader">
    <!-- Note label container-->
    <div class="client-note-upper-placeholder-container">
        <!-- Date and time -->
        <div class="client-note-top-left-placeholder">
            <div class="moving-skeleton-loader-part-wrapper">
                <div class="moving-skeleton-loader-part"></div>
            </div>
        </div>
        <!-- Note autor -->
        <div class="client-note-top-right-placeholder">
            <div class="moving-skeleton-loader-part-wrapper">
                <div class="moving-skeleton-loader-part"></div>
            </div>
        </div>
    </div>
    <!-- Textarea container-->
    <div class="client-note-lower-placeholder-container">
        <!-- Text line inside textarea container -->
        <div class="text-line-skeleton-loader client-note-first-text-line-placeholder">
            <div class="moving-skeleton-loader-part-wrapper">
                <div class="moving-skeleton-loader-part"></div>
            </div>
        </div>
        <div class="text-line-skeleton-loader client-note-second-text-line-placeholder">
            <div class="moving-skeleton-loader-part-wrapper">
                <div class="moving-skeleton-loader-part"></div>
            </div>
        </div>
    </div>
</div>`;
}