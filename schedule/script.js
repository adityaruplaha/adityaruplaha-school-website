function details(timestamp, subject) {
    var modal = document.createElement("div")
    modal.classList.add("modal", "active");
    var modal_box = document.createElement("div")
    var modal_content = document.createElement("div")
    modal_box.classList.add("card", "box");
    modal_box.style.paddingLeft = '1.5rem';
    var modal_close = document.createElement("span")
    modal_close.classList.add("close");
    modal_close.innerHTML = "&times;";
    modal_content.innerHTML = "Loading your data...";
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

    // Fetch data
    const xhr = new XMLHttpRequest(),
        method = "GET",
        url = "class_info.php?timestamp=" + timestamp + "&subject=" + subject;

    xhr.open(method, url, true);
    xhr.send();
    xhr.onreadystatechange = function () {
        var status = xhr.status;
        if (status === 0 || (status >= 200 && status < 400)) {
            data = JSON.parse(xhr.responseText);
            let meet_regex = new RegExp('(https:\/\/meet.google.com\/[a-z]{3}-[a-z]{4}-[a-z]{3})', 'g');
            let meet_links = data.desc.match(meet_regex);
            meet_a = document.createElement('a');
            if (meet_links.length) {
                meet_a.href = meet_links.pop();
                meet_a.innerHTML = "Join with Google Meet";
                meet_a.classList.add("bigfont");
            }
            header = document.createElement('h2');
            header.innerHTML = data.name.replace(/^[A-Za-z]+:\s/, '');
            modal_content.innerHTML = '';
            modal_content.appendChild(header);
            modal_content.appendChild(meet_a);
        } else {
            modal_content.innerHTML = "<p class='red'>Failed to fetch data.</p>";
        }
    }
}
