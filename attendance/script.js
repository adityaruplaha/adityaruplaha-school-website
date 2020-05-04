/* Define function for escaping user input to be treated as
a literal string within a regular expression */
function escapeRegExp(string) {
  return string.replace(/[.*+?^${}()|[\]\\]/g, '\\$&');
}

/* Define functin to find and replace specified term with replacement string */
function replaceAll(str, term, replacement) {
  return str.replace(new RegExp(escapeRegExp(term), 'g'), replacement);
}

function beautify() {
  document.body.innerHTML = replaceAll(
    document.body.innerHTML, '<td>0</td>',
    '<td class=\'red\' align=\'center\'><b>A</b></td>');
  document.body.innerHTML = replaceAll(
    document.body.innerHTML, '<td>1</td>',
    '<td class=\'green\' align=\'center\'><b>P</b></td>');
  document.body.innerHTML = replaceAll(
    document.body.innerHTML, '<td>NULL</td>', '<td align=\'center\'></td>');
  // Highlight default tab
  document.getElementById('dbut').style = "background-color: #343434; font-weight: 600;";
}

function show(button, tab) {
  for (let e of document.body.getElementsByClassName('tab')) {
    e.style.display = 'none';
  }
  document.getElementById('dbut').removeAttribute('style');
  document.getElementById('sbut').removeAttribute('style');
  document.getElementById('stbut').removeAttribute('style');
  button.style = "background-color: #343434; font-weight: 600;";
  document.getElementById(tab).style.display = 'block';
}
