export function getNoteHtml(noteId, noteCreatedAt, userMutationRight, userFullName, message) {
    // ANY NOTE HTML THAT IS CHANGED BELOW HAS TO ADAPTED
    // IN client-read-create-note.js AS WELL (addNewNoteTextarea, populateNewNoteDomAttributes)
    return `<div id="note${noteId}-container" class="note-container">
                <label for="note${noteId}" class="discrete-label textarea-label">
                    <span class="note-left-side-label-span">${noteCreatedAt}</span>
                    ${// Following function is in paranthesis and called with () at the end to be interpreted 
                        (() => {
                            if (userHasMutationRights(userMutationRight)) {
                                return `<img class="delete-note-btn" alt="delete" src="assets/general/img/del-icon.svg"
                                                                          data-note-id="${noteId}">`;
                            }
                            return '';
                        })()}
                    <span class="discrete-text note-right-side-label-span">${userFullName}</span>
                </label>
                <!-- Extra div necessary to position circle loader to relative parent without taking label into account -->
                <div class="relative">
                    <!-- Textarea opening and closing has to be on the same line to prevent unnecessary line break -->
                    <textarea class="auto-resize-textarea" id="note${noteId}"
                              data-note-id="${noteId}"
                              minlength="4" maxlength="500"
                              data-editable="${userHasMutationRights(userMutationRight) ? '1' : '0'}"
                              name="message">${message}</textarea>
                    <div class="circle-loader client-read" data-note-id="${noteId}">
                        <div class="checkmark draw"></div>
                    </div>
                </div>
            </div>`;
}

// todo test xss html as message to see if needed to escape server side first
/**
 * Testing if user has rights logic in own function
 * to be easier to adapt later on.
 *
 * @param userMutationRight
 * @return {boolean}
 */
function userHasMutationRights(userMutationRight) {
    return userMutationRight === 'all';
}
