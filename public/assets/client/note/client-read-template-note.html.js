import {escapeHtml} from "../../general/general-js/functions.js?v=0.2.0";

export function getNoteHtml(note) {
    // Thanks https://www.youtube.com/watch?v=Mus_vwhTCq0 for this syntax
    const {id, createdAt, privilege, hidden, userFullName, message, isClientMessage} = note;

    // ANY NOTE HTML THAT IS CHANGED BELOW HAS TO ADAPTED
    // IN client-read-create-note.js AS WELL (addNewNoteTextarea, populateNewNoteDomAttributes)
    return `<div id="note-${id}-container" 
              class="note-container ${hidden === 1 || hidden === '1' ? 'hidden-note' : ''}">
                <label for="note-${id}" data-note-id="${id}" class="bigger-select-label textarea-label">
                    <a href="${window.location.href}#note-${id}-container" 
                        class="note-left-side-label no-style-a">${createdAt}</a>
                    ${/*Show active eye icon if hidden*/ hidden === 1 || hidden === '1' ? `<img 
                        class="btn-above-note hide-note-btn ${userHasPrivilegeTo(privilege, 'U') ? `` : `
                            not-clickable` /*Add not clickable class when not allowed to update*/}" alt="hide" 
                        style="display: inline-block" src="assets/general/general-img/eye-icon-active.svg"
                        >` : /* Else the non-active one if allowed*/ userHasPrivilegeTo(privilege, 'U') ? `
                        <img class="btn-above-note hide-note-btn" alt="hide" src="assets/general/general-img/eye-icon.svg">` : ''}
                    ${/*Show delete button */ userHasPrivilegeTo(privilege, 'D') ? `<img 
                        class="btn-above-note delete-note-btn" alt="delete" src="assets/general/general-img/del-icon.svg">` : ''}
                    <span class="discrete-text note-right-side-label-span 
                    ${isClientMessage === 1 ? 'client-message-label' : ''}">${escapeHtml(userFullName)}</span>
                </label>
                <!-- Extra div necessary to position circle loader to relative parent without taking label into account -->
                <div class="relative">
                   ${userHasPrivilegeTo(privilege, 'R') ? '' : `<div class="hidden-textarea-overlay"></div>`}
                    <!-- Textarea opening and closing has to be on the same line to prevent unnecessary line break -->
                    <textarea class="auto-resize-textarea ${isClientMessage === 1 ? 'client-message-textarea' : ''}
                      ${/* Blur note text if not allowed to read*/ userHasPrivilegeTo(privilege, 'R') ? `
                      ` : 'hidden-note-message'}"
                              id="note-${id}"
                              data-note-id="${id}"
                              minlength="4" maxlength="1000" required
                              data-editable="${userHasPrivilegeTo(privilege, 'U') ? '1' : '0'}"
                              name="message">${escapeHtml(message)}</textarea>
                    <div class="circle-loader client-note" data-note-id="${id}">
                        <div class="checkmark draw"></div>
                    </div>
                </div>
            </div>`;
}

/**
 * Check if user has required privilege
 * If the received privilege contains one
 * of the following letters, it means:
 *  D - Delete - Highest privilege, may also do other actions
 *  U - Update - May also create and read but not delete
 *  C - Create - May also read
 *  R - Read - May only read but do nothing else
 *  *
 * @param {string} actualPrivilege
 * @param {string} requiredPrivilege
 * @return {boolean}
 */
function userHasPrivilegeTo(actualPrivilege, requiredPrivilege) {
    switch (requiredPrivilege) {
        // Starting from the highest privilege to the lowest
        case 'D':
            return actualPrivilege.includes('D');
        case 'U':
            return actualPrivilege.includes('U');
        case 'C':
            return actualPrivilege.includes('C');
        case 'R':
            return actualPrivilege.includes('R');
        default:
            return false;
    }
}

export function getClientNoteLoadingPlaceholderHtml() {
    return `<div class="client-note-loading-placeholder">
    <!-- Note label container-->
    <div class="client-note-upper-placeholder-container">
        <!-- Date and time -->
        <div class="client-note-top-left-placeholder">
            <div class="moving-loading-placeholder-part-wrapper">
                <div class="moving-loading-placeholder-part"></div>
            </div>
        </div>
        <!-- Note autor -->
        <div class="client-note-top-right-placeholder">
            <div class="moving-loading-placeholder-part-wrapper">
                <div class="moving-loading-placeholder-part"></div>
            </div>
        </div>
    </div>
    <!-- Textarea container-->
    <div class="client-note-lower-placeholder-container">
        <!-- Text line inside textarea container -->
        <div class="text-line-content-placeholder client-note-first-text-line-placeholder">
            <div class="moving-loading-placeholder-part-wrapper">
                <div class="moving-loading-placeholder-part"></div>
            </div>
        </div>
        <div class="text-line-content-placeholder client-note-second-text-line-placeholder">
            <div class="moving-loading-placeholder-part-wrapper">
                <div class="moving-loading-placeholder-part"></div>
            </div>
        </div>
    </div>
</div>`;
}