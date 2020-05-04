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
        document.body.innerHTML = replaceAll(document.body.innerHTML, "<th>ExtraSub</th>", "<th>Subject Chosen</th>");
    }
    </script>
    <link rel='stylesheet' type='text/css' href='stylesheet.css' />
</head>

<body onload="clean()">
    <?php
    $db_host = 'localhost';
    $db_user = 'prog_access';
    $db_pwd = '';

    $database = 'school';
    $table = 'xii_sc_a';

    $conn = new mysqli($db_host, $db_user, $db_pwd, $database);

    // Check connection
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    // Query
    $result = $conn->query("SELECT LPAD(row_number() over ( order by Name), 2, 0) `Serial No.`, Name, ExtraSub FROM {$table}");
    if (!$result) {
        die("Query to show fields from table failed");
    }

    $fields_num = $result->field_count;

    echo "<h1 align='center'>XII Sc A - Name List</h1>";
    echo "<hr/>";

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