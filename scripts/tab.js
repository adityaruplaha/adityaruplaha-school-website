function show(button, tab) {
    for (let e of document.body.getElementsByClassName('tab')) {
        e.classList.remove("active");
    }
    for (let e of document.body.getElementsByClassName('tab_button')) {
        e.classList.remove("active");
    }
    button.classList.add("active")
    document.getElementById(tab).classList.add("active")
}

function autoload(n = 0) {
    if (document.body.getElementsByClassName('tab active').length == 0) {
        document.body.getElementsByClassName('tab')[n].classList.add("active");
        document.body.getElementsByClassName('tab_button')[n].classList.add("active");
    }
}
