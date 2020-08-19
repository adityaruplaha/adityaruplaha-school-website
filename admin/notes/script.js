function edit_note(id, newcontent) {
    $.ajax
    post("edit_note.php", "Content=" + encodeURIComponent(newcontent) + "&ID=" + id, function () { window.location.reload() })
}

function delete_note(id) {
    post("delete_note.php", "ID=" + id, function () { window.location.reload() })
}

function edit(button, id) {
    div_to_edit = button.parentElement.parentElement.children[1];
    textarea = document.createElement("textarea");
    textarea.classList.add("edit");
    textarea.value = div_to_edit.innerText;
    textarea.style.height = window.getComputedStyle(div_to_edit, null).height;
    icons = document.createElement("span");
    icons.classList.add("iconholders");
    icons.innerHTML += '<span class="iconify green" data-icon="carbon:checkmark-filled" data-inline="false" onclick="edit_note(' + id + ', this.parentElement.parentElement.firstElementChild.value)"></span >';
    icons.innerHTML += '&nbsp;&nbsp;';
    icons.innerHTML += '<span class="iconify red" data-icon="emojione-monotone:cross-mark-button" data-inline="false" onclick="window.location.reload();"></span>';

    div_to_edit.parentElement.lastElementChild.classList.add("off");
    div_to_edit.innerHTML = '';
    div_to_edit.appendChild(textarea);
    div_to_edit.appendChild(icons);

    autosize(document.querySelectorAll('textarea'));
}

document.addEventListener('DOMContentLoaded', function () {
    autosize(document.querySelectorAll('textarea'));
}, false);
