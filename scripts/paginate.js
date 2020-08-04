function chunk(arr, len) {

    var chunks = [],
        i = 0,
        n = arr.length;

    while (i < n) {
        chunks.push(arr.slice(i, i += len));
    }

    return chunks;
}

function paginate(table, rows) {
    var elements = Array.from(table.firstElementChild.children);
    var header_row = elements.shift();
    var chunks = chunk(elements, rows);
    var i = 0;
    var navbar = document.createElement("table");
    navbar.classList.add('pgnav', 'unbordered', 'smallfont');
    navlist = document.createElement("tr");
    navbar.appendChild(navlist);
    table.parentElement.appendChild(navbar);
    table.parentElement.appendChild(document.createElement('br'));

    button = document.createElement('td');
    button.setAttribute('onclick', 'show_page("' + table.id + '", 0, null);');
    button.classList.add(table.id + "__navbutton", 'pg_button');
    button.innerHTML = "<<";
    navlist.appendChild(button);

    button = document.createElement('td');
    button.setAttribute('onclick', 'show_page("' + table.id + '", current_page("' + table.id + '") - 1, null);');
    button.classList.add(table.id + "__navbutton", 'pg_button');
    button.innerHTML = "<";
    navlist.appendChild(button);

    chunks.forEach(c => {
        var t = document.createElement("table");
        t.className = table.className;
        t.appendChild(header_row.cloneNode(true))
        c.forEach(tr => {
            t.appendChild(tr.cloneNode(true))
        });
        t.id = table.id + '__page' + i;
        t.classList.add(table.id + '__page')
        table.parentElement.appendChild(t);

        button = document.createElement('td');
        button.setAttribute('onclick', 'show_page("' + table.id + '", ' + i + ', this);');
        button.classList.add(table.id + "__navbutton", 'pg_button');
        button.innerHTML = i + 1;
        navlist.appendChild(button);
        i += 1;
    });

    button = document.createElement('td');
    button.setAttribute('onclick', 'show_page("' + table.id + '", current_page("' + table.id + '") + 1, null);');
    button.classList.add(table.id + "__navbutton", 'pg_button');
    button.innerHTML = ">";
    navlist.appendChild(button);

    button = document.createElement('td');
    button.setAttribute('onclick', 'show_page("' + table.id + '", ' + (i - 1) + ', null);');
    button.classList.add(table.id + "__navbutton", 'pg_button');
    button.innerHTML = '>>';
    navlist.appendChild(button);

    table.remove();
}

function current_page(table_id) {
    return Number(document.getElementsByClassName(table_id + '__navbutton active')[0].innerHTML) - 1;
}

function pages(table_id) {
    return document.getElementsByClassName(table_id + '__page').length;
}

function show_page(table_id, page_no, button = null) {
    var max_page_no = pages(table_id) - 1;
    page_no = Math.min(page_no, max_page_no);
    page_no = Math.max(page_no, 0);

    for (let page of document.getElementsByClassName(table_id + '__page')) {
        page.classList.add('invisible');
    }
    document.getElementById(table_id + '__page' + page_no).classList.remove('invisible');

    if (!button) {
        button = document.getElementsByClassName(table_id + '__navbutton')[page_no + 2];
    }
    for (let b of document.getElementsByClassName(table_id + '__navbutton')) {
        b.classList.remove('active');
    }
    button.classList.add('active');

    if (page_no == 0) {
        document.getElementsByClassName(table_id + '__navbutton')[0].classList.add("disabled");
        document.getElementsByClassName(table_id + '__navbutton')[1].classList.add("disabled");
    } else {
        document.getElementsByClassName(table_id + '__navbutton')[0].classList.remove("disabled");
        document.getElementsByClassName(table_id + '__navbutton')[1].classList.remove("disabled");
    }

    if (page_no == max_page_no) {
        document.getElementsByClassName(table_id + '__navbutton')[max_page_no + 3].classList.add("disabled");
        document.getElementsByClassName(table_id + '__navbutton')[max_page_no + 4].classList.add("disabled");
    } else {
        document.getElementsByClassName(table_id + '__navbutton')[max_page_no + 3].classList.remove("disabled");
        document.getElementsByClassName(table_id + '__navbutton')[max_page_no + 4].classList.remove("disabled");
    }
}
