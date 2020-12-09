function render_json(json) {
    var modal = document.createElement("div")
    modal.classList.add("modal", "active");
    var modal_box = document.createElement("div")
    var modal_content = document.createElement("div")
    modal_box.classList.add("card", "box");
    modal_box.style.paddingLeft = '1.5rem';
    var modal_close = document.createElement("span")
    modal_close.classList.add("close");
    modal_close.innerHTML = "&times;";
    modal_content.appendChild(renderjson.set_show_to_level('all')(json));
    modal_close.onclick = function () {
        modal.remove();
    }
    modal.onclick = function () {
        modal.remove();
    }
    modal_box.appendChild(modal_close);
    modal_box.appendChild(modal_content);
    modal.appendChild(modal_box);

    document.body.appendChild(modal);
}
