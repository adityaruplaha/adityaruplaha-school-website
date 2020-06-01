<?php
require_once "../../login.php";
require_once "../../teacher/defs.php";

use \ScA\Student\TGLogin\TGLogin;
use \ScA\Teacher;

$is_teacher = Teacher\is_logged_in();

$s = TGLogin::from_cookie();
if ($s != NULL) {
    $s = new \ScA\Student\Student(NULL, $s->id);
    if (!$s->has_privileges("Admin")) {
        $s = NULL;
    }
}
$is_logged_in = ($s != NULL) || $is_teacher;

if (!$is_logged_in) {
    header("Location: ../../?nauth");
    exit;
}
?>

<html>

<head>
    <meta charset="utf-8">
    <title>XII Sc A - Student Details</title>
    <script>
    /* Define function for escaping user input to be treated as 
    a literal string within a regular expression */
    function escapeRegExp(string) {
        return string.replace(/[.*+?^${}()|[\]\\]/g, "\\$&");
    }

    /* Define functin to find and replace specified term with replacement string */
    function replaceAll(str, term, replacement) {
        return str.replace(new RegExp(escapeRegExp(term), 'g'), replacement);
    }

    function clean() {
        document.body.innerHTML = replaceAll(document.body.innerHTML, "<td>pe</td>", "<td>Physical Education</td>");
        document.body.innerHTML = replaceAll(document.body.innerHTML, "<td>bn</td>", "<td>Bengali</td>");
        document.body.innerHTML = replaceAll(document.body.innerHTML, "<td>hi</td>", "<td>Hindi</td>");
        document.body.innerHTML = replaceAll(document.body.innerHTML, "<td>Passed</td>",
            "<td align='center'>Passed</td>");
        document.body.innerHTML = replaceAll(document.body.innerHTML, "<td>Failed</td>",
            "<td class='red' align='center'>Failed</td>");
        document.body.innerHTML = replaceAll(document.body.innerHTML, "<td>Retest</td>",
            "<td class='yellow' align='center'>Retest</td>");
        document.body.innerHTML = replaceAll(document.body.innerHTML, "<th>ExtraSub</th>", "<th>Subject Chosen</th>");
    }
    </script>
    <link rel='stylesheet' type='text/css' href='stylesheet.css' />
</head>

<body onload="clean()">
    <?php
    require_once "../../defs.php";

    use const ScA\DB;
    use const ScA\DB_HOST;
    use const ScA\DB_PWD;
    use const ScA\DB_USER;

    $conn = new mysqli(DB_HOST, DB_USER, DB_PWD, DB);;

    // Check connection
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    // Query
    $result = $conn->query("SELECT LPAD(row_number() over ( order by Name), 2, 0) `Serial No.`, Name, EMail, ExtraSub, Status FROM info");
    if (!$result) {
        die("Query to show fields from table failed");
    }

    $fields_num = $result->field_count;

    echo "<h1 align='center'>XII Sc A - Student Details</h1>";
    echo "<hr/>";
    date_default_timezone_set("Asia/Kolkata");
    echo "<p align='center'><i>Report generated on " . date("d M Y h:i:sa") . " IST.</i></p>";

    echo "<div>";
    echo "<table border='1'><tr>";
    // printing table headers
    for ($i = 0; $i < $fields_num; $i++) {
        $field = $result->fetch_field();
        echo "<th>{$field->name}</th>";
    }
    echo "</tr>\n";
    // printing table rows
    while ($row = $result->fetch_row()) {
        echo "<tr>";

        // $row is array... foreach( .. ) puts every element
        // of $row to $cell variable
        foreach ($row as $cell)
            echo "<td>$cell</td>";

        echo "</tr>\n";
    }
    $result->free();
    $conn->close();
    echo "</table>";
    echo "</div>";

    ?>
</body>

</html>