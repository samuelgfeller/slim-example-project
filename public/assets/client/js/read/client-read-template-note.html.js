import {escapeHtml} from "../../../general/js/functions.js";

export function getNoteHtml(noteId, noteCreatedAt, privilege, userFullName, message) {
    // ANY NOTE HTML THAT IS CHANGED BELOW HAS TO ADAPTED
    // IN client-read-create-note.js AS WELL (addNewNoteTextarea, populateNewNoteDomAttributes

    return `<div id="note${noteId}-container" class="note-container">
                <label for="note${noteId}" class="bigger-select-label textarea-label">
                    <span class="note-left-side-label-span">${noteCreatedAt}</span>
                    ${// Following function is in paranthesis and called with () at the end to be interpreted 
        (() => {
            if (userHasPrivilegeTo(privilege, 'D')) {
                return `<img class="delete-note-btn" alt="delete" src="assets/general/img/del-icon.svg"
                                                                          data-note-id="${noteId}">`;
            }
            return '';
        })()}
                    <span class="discrete-text note-right-side-label-span">${escapeHtml(userFullName)}</span>
                </label>
                <!-- Extra div necessary to position circle loader to relative parent without taking label into account -->
                <div class="relative">
                    <!-- Textarea opening and closing has to be on the same line to prevent unnecessary line break -->
                    <textarea class="auto-resize-textarea" id="note${noteId}"
                              data-note-id="${noteId}"
                              minlength="4" maxlength="500" required
                              data-editable="${userHasPrivilegeTo(privilege, 'U') ? '1' : '0'}"
                              name="message">${escapeHtml(message)}</textarea>
                    <div class="circle-loader client-read" data-note-id="${noteId}">
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