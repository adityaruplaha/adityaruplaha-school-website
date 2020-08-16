function telemetry_updated(slider, id) {
    post("telemetry_update.php", "telemetry=" + slider.value + "&id=" + id, function () { })
    slider.classList.remove("mode0")
    slider.classList.remove("mode1")
    slider.classList.remove("mode2")
    slider.classList.add("mode" + slider.value)
}

function login_updated(slider, id) {
    post("login_update.php", "block_login=" + slider.value + "&id=" + id, function () { })
    slider.classList.remove("mode0")
    slider.classList.remove("mode1")
    slider.classList.add("mode" + slider.value)
}

function resource_updated(slider, id) {
    post("resource_update.php", "block_resource_access=" + slider.value + "&id=" + id, function () { })
    slider.classList.remove("mode0")
    slider.classList.remove("mode1")
    slider.classList.add("mode" + slider.value)
}
