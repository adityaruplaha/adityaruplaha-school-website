function show_modal(modal_element) {
    modal_element.classList.add("active");
    window.onclick = function (event) {
        if (event.target == modal_element) {
            hide_modal(modal_element)
        }
    }
}

function hide_modal(modal_element) {
    modal_element.classList.remove("active");
}
