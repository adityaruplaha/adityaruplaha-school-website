<?php
require_once "../../login.php";

use \ScA\Student\TGLogin\TGLogin;

$s = TGLogin::from_cookie();
if ($s != NULL) {
    $s = new \ScA\Student\Student(NULL, $s->id);
    if (!$s->has_privileges("Admin")) {
        $s = NULL;
    }
}

if ($s != NULL) {
    $s->report_url_visit($_SERVER['PHP_SELF']);
}

$is_logged_in = ($s != NULL);

if (!$is_logged_in) {
    header("Location: ../../?nauth");
    exit;
}
?>

<!DOCTYPE html>
<html lang='en'>

<head>
    <meta charset="utf-8">
    <title>XII Sc A - Trello Upload Recommendations</title>
    <link rel='stylesheet' type='text/css' href='/sc_a/themes/dark/base.css' />
    <link rel='stylesheet' type='text/css' href='/sc_a/themes/dark/tables.css' />
</head>

<body>
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
    $result = $conn->query("SELECT * FROM trello_upload_recommendations");
    if (!$result) {
        die("Query to show fields from table failed");
    }

    $fields_num = $result->field_count;

    echo "<h1 class='center'>XII Sc A - Trello Upload Recommendations</h1>";
    echo "<hr/>";
    date_default_timezone_set("Asia/Kolkata");
    echo "<p class='center'>
    <i>Report generated on " . date("d M Y h:i:sa") . " IST.</i>
    <br/><br/>
    The candidates most deserving of this task appear at the top.
    </p>";

    echo "<div>";
    echo "<table class='semibordered center autowidth'><tr>";
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