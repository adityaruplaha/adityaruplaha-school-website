function show(tab) {
    for (let e of document.body.getElementsByClassName('tab')) {
        e.style.display = 'none';
    }
    document.getElementById(tab).style.display = 'block';
}

function clean() {
    for (let e of document.body.getElementsByClassName('tab')) {
        e.style.display = 'none';
    }
}
