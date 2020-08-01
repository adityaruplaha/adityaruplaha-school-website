function telemetry_updated(slider, id) {
    post("telemetry_update.php", "telemetry=" + slider.value + "&id=" + id, function () { })
    slider.classList.remove("mode0")
    slider.classList.remove("mode1")
    slider.classList.remove("mode2")
    slider.classList.add("mode" + slider.value)
}
