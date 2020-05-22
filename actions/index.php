<?php

require_once "../login.php";
require_once "../teacher/defs.php";

use \ScA\Student\TGLogin\TGLogin;
use \ScA\Teacher;

$is_logged_in = (TGLogin::from_cookie() != NULL) || (Teacher\is_logged_in());

if (!$is_logged_in) {
    header("Location: ../?nauth");
    exit;
}


require_once "../defs.php";

use const ScA\DB;
use const ScA\DB_HOST;
use const ScA\DB_PWD;
use const ScA\DB_USER;

$table = 'actions';

$conn = new mysqli(DB_HOST, DB_USER, DB_PWD, DB);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

?>

<html lang='en'>

<head>
    <meta charset="utf-8">
    <title>XII Sc A - Action Log</title>
    <script src='script.js'>
    </script>
    <link rel='stylesheet' type='text/css' href='stylesheet.css' />
</head>

<body onload="clean()">

    <h1 align='center'>XII Sc A - Action Log</h1>
    <hr />
    <p align='center'>
        <i>
            <?php
            date_default_timezone_set("Asia/Kolkata");
            echo "Report generated on " . date("d M Y h:i:sa") . " IST."
            ?>
        </i>
        <hr />
    </p>

    <?php
    // Query
    $result = $conn->query("SELECT * FROM {$table} ORDER BY `{$table}`.`Timestamp` ASC");

    if (!$result) {
        die("Query to show fields from table failed.");
    }

    echo "
        <div class='tab' id='{$sub}'>
        <table>
            <tr>
                <th>Timestamp</th>
                <th>Action</th>
                <th>Log</th>
            </tr>";

    while ($action = $result->fetch_assoc()) {
        echo "<tr>";

        echo "<td>" . $action["Timestamp"] . "</td>";
        echo "<td>" . $action["Action"] . "</td>";

        if ($r = $action["Log"]) {
            $r = str_replace("\n", "\n<br/>", htmlspecialchars($r));
            echo "<td class='code'>{$r}</td>";
        } else {
            echo "<td></td>";
        }

        echo "</tr>";
    }

    echo "</table>";
    $result->free();


    ?>
</body>

</html>