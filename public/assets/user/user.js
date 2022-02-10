let firstNameEditIco = document.getElementById('edit-first-name-ico');
let firstNameValSpan = document.getElementById('first-name-val');

firstNameEditIco.addEventListener('click', function () {
    let firstNameString = firstNameValSpan.innerText;
    let firstNameInput = document.createElement('input');
    firstNameInput.type = 'text';
    firstNameInput.className = 'form-input';
    firstNameInput.value = firstNameString;
    firstNameInput.size = firstNameString.length + 13;
    firstNameValSpan.parentNode.appendChild(firstNameInput);
    firstNameValSpan.parentNode.removeChild(firstNameValSpan);
    firstNameEditIco.style.display = 'none';
});